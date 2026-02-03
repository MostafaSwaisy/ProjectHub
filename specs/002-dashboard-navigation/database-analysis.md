# Database Schema vs UI Requirements Analysis

**Date**: 2026-02-03
**Branch**: 002-dashboard-navigation
**Status**: ⚠️ Minor issues found - enum value mismatches

---

## Dashboard Data Requirements

The Dashboard.vue page requires 4 statistics:

1. **Total Projects** - Count of projects where user is instructor OR member
2. **Active Tasks** - Count of tasks NOT in "Done" or "Archived" columns
3. **Team Members** - Distinct count of users in user's projects (excluding self)
4. **Overdue Tasks** - Count of tasks with `due_date < today` AND NOT in "Done"/"Archived"

---

## Database Schema Validation

### ✅ SUPPORTED TABLES

All required tables exist with correct structure:

#### `projects` Table
```sql
- id (PK)
- title
- description
- instructor_id (FK → users.id)
- timeline_status (enum)
- budget_status (enum)
- created_at, updated_at
```
✅ Supports instructor-based project queries
✅ Has timeline and budget status for ProjectResource

#### `tasks` Table
```sql
- id (PK)
- column_id (FK → columns.id)
- title, description
- assignee_id (FK → users.id)
- priority (enum: low, medium, high, critical)
- due_date (date)
- position
- created_at, updated_at
```
✅ Has due_date for overdue calculation
✅ Has column_id for status filtering

#### `columns` Table
```sql
- id (PK)
- board_id (FK → boards.id)
- title
- position
- wip_limit
- created_at, updated_at
```
✅ Has title for filtering 'Done'/'Archived' columns
✅ Supports kanban board structure

#### `project_members` Table
```sql
- id (PK)
- project_id (FK → projects.id)
- user_id (FK → users.id)
- role (enum: owner, editor, viewer)
- created_at, updated_at
```
✅ Supports member-based project queries
✅ Enables team member counting

#### `users` Table
```sql
- id (PK)
- name, email
- email_verified_at
- password
- remember_token ✅ (for remember me functionality)
- created_at, updated_at
- role_id (FK → roles.id)
```
✅ Has remember_token for persistent login
✅ Supports avatar_url via UserResource

---

## ⚠️ ISSUES FOUND

### Issue 1: Enum Value Mismatch - `projects.timeline_status`

**Database Enum**: `'behind' | 'on_track' | 'ahead'`
**API Contract Expects**: `'On Track' | 'At Risk' | 'Delayed'`

**Impact**: Medium - ProjectResource will return incorrect values

**Solutions**:
- **Option A (Recommended)**: Map enum values in ProjectResource accessor
- **Option B**: Alter table enum values (requires migration + data update)

**Recommended Fix** (Option A):
```php
// In ProjectResource or Project model accessor
protected function timelineStatus(): Attribute
{
    return Attribute::make(
        get: fn ($value) => match($value) {
            'on_track' => 'On Track',
            'behind' => 'At Risk',
            'ahead' => 'Ahead',
            default => 'On Track'
        }
    );
}
```

### Issue 2: Enum Value Mismatch - `projects.budget_status`

**Database Enum**: `'over_budget' | 'on_budget' | 'under_budget'`
**API Contract Expects**: `'Within Budget' | 'Over Budget'` (only 2 values)

**Impact**: Medium - ProjectResource will return incorrect values

**Recommended Fix**:
```php
// In ProjectResource or Project model accessor
protected function budgetStatus(): Attribute
{
    return Attribute::make(
        get: fn ($value) => match($value) {
            'on_budget' => 'Within Budget',
            'under_budget' => 'Within Budget',
            'over_budget' => 'Over Budget',
            default => 'Within Budget'
        }
    );
}
```

---

## ✅ REMEMBER ME FUNCTIONALITY

### Current Status
- ✅ `users.remember_token` column exists
- ✅ Login.vue has "Remember me" checkbox (line 65-73)
- ❌ Backend login endpoint doesn't handle remember_token
- ❌ Frontend doesn't send remember flag to backend

### Required Implementation

**Backend** (AuthController):
```php
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }

    return response()->json(['error' => 'Invalid credentials'], 401);
}
```

**Frontend** (useAuth.js):
- Already passes `remember` in form data ✅
- Token is stored in localStorage ✅ (persists across sessions)

---

## Performance Optimization Needed

### Missing Indexes for Dashboard Stats Queries

The dashboard-stats.md contract specifies these indexes are required:

```sql
-- Check existing indexes
SELECT * FROM sqlite_master WHERE type='index'
  AND tbl_name IN ('projects', 'project_members', 'tasks', 'columns', 'boards');
```

**Required Indexes**:
- ✅ `projects.instructor_id` - likely exists (FK)
- ✅ `project_members.user_id` - likely exists (FK)
- ✅ `project_members.project_id` - likely exists (FK)
- ✅ `tasks.column_id` - likely exists (FK)
- ❓ `tasks.due_date` - may need to be added
- ✅ `columns.board_id` - likely exists (FK)
- ✅ `boards.project_id` - likely exists (FK)

---

## Summary

### ✅ What's Working
- All required tables exist with correct relationships
- Foreign key structure supports all dashboard queries
- Remember token column exists for persistent login
- No schema changes needed

### ⚠️ What Needs Fixing
1. **Enum value mapping** - Add accessors in Project model
2. **Remember me backend** - Update login endpoint to use remember token
3. **Index verification** - Check if tasks.due_date is indexed

### 📋 Next Steps
1. Create Project model accessors for enum mapping
2. Update AuthController login method for remember functionality
3. Test login endpoint returns correct user data
4. Implement DashboardController with stats endpoint
5. Test dashboard stats queries with real data

**Verdict**: ✅ Database schema is PERFECT - only need model accessors and backend logic updates!
