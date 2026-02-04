# Test Users Documentation

**Last Updated**: 2026-02-04
**Database Version**: After running `php artisan migrate:fresh --seed`

---

## Quick Reference - Login Credentials

| User | Email | Password | Role | Projects Owned | Projects Member |
|------|-------|----------|------|----------------|-----------------|
| Admin User | `admin@example.com` | `password` | Admin | 3 | 0 |
| John Developer | `john@example.com` | `password` | Instructor | 0 | 2 |
| Jane Designer | `jane@example.com` | `password` | Instructor | 0 | 2 |
| Bob Tester | `bob@example.com` | `password` | Instructor | 0 | 1 |

---

## Detailed User Information

### 1. Admin User (Primary Test Account)

**Credentials:**
- Email: `admin@example.com`
- Password: `password`
- Role: Admin (role_id: 1)

**Owns Projects:**
1. Website Redesign
2. Mobile App Development
3. API Integration

**Dashboard Stats (as owner):**
- Total Projects: 3
- Active Tasks: ~24 (tasks not in "Done" column)
- Team Members: 3 (John, Jane, Bob across all projects)
- Overdue Tasks: 6 (deliberately set for testing)

**Use This Account For:**
- Testing project owner features
- Dashboard statistics with full data
- Managing team members
- Creating/editing/deleting projects
- Full administrative access

---

### 2. John Developer (Team Member)

**Credentials:**
- Email: `john@example.com`
- Password: `password`
- Role: Instructor (role_id: 2)

**Project Memberships:**
- **Website Redesign** - Editor role
- **Mobile App Development** - Editor role

**Assigned Tasks:**
- Setup development environment (To Do)
- Database schema design (To Do)
- Implement authentication (In Progress)
- Fix critical bug in payment flow (In Progress - OVERDUE)
- Setup CI/CD pipeline (Done)

**Use This Account For:**
- Testing project member (editor) features
- Task assignment and updates
- Viewing projects as a team member
- Testing permissions (editor level)

---

### 3. Jane Designer (Team Member)

**Credentials:**
- Email: `jane@example.com`
- Password: `password`
- Role: Instructor (role_id: 2)

**Project Memberships:**
- **Website Redesign** - Editor role
- **API Integration** - Editor role

**Assigned Tasks:**
- Create wireframes (Backlog)
- Design system components (In Progress)
- Create project documentation (Done)
- Initial design mockups (Done)

**Use This Account For:**
- Testing design-related workflows
- Multi-project membership scenarios
- Editor role permissions

---

### 4. Bob Tester (Team Member)

**Credentials:**
- Email: `bob@example.com`
- Password: `password`
- Role: Instructor (role_id: 2)

**Project Memberships:**
- **Mobile App Development** - Viewer role

**Assigned Tasks:**
- API endpoint documentation (To Do)
- Code review: User dashboard (Review)
- Security audit (Review - OVERDUE)

**Use This Account For:**
- Testing viewer-only permissions
- Limited access scenarios
- Quality assurance workflows

---

## Project Details

### Project 1: Website Redesign
- **Owner**: Admin User
- **Team Members**: John (Editor), Jane (Editor)
- **Status**: On Track, On Budget
- **Board**: Website Redesign Board
- **Total Tasks**: ~13 tasks across 5 columns

### Project 2: Mobile App Development
- **Owner**: Admin User
- **Team Members**: John (Editor), Bob (Viewer)
- **Status**: Behind Schedule, On Budget
- **Board**: Mobile App Development Board
- **Total Tasks**: ~13 tasks across 5 columns

### Project 3: API Integration
- **Owner**: Admin User
- **Team Members**: Jane (Editor)
- **Status**: On Track, On Budget
- **Board**: API Integration Board
- **Total Tasks**: ~13 tasks across 5 columns

---

## Task Distribution by Status

### Backlog (2 per project = 6 total)
- Research and planning tasks
- Unassigned or low priority
- Future work items

### To Do (3 per project = 9 total)
- Ready to start
- Assigned to team members
- High priority items

### In Progress (3 per project = 9 total)
- Currently being worked on
- **Includes 1 OVERDUE task per project** (for testing)
- Critical and high priority

### Review (2 per project = 6 total)
- Code reviews and audits
- **Includes 1 OVERDUE task per project** (for testing)
- Pending approval

### Done (3 per project = 9 total)
- Completed tasks
- Should NOT count in active or overdue stats
- Historical reference

---

## Testing Scenarios

### 1. Dashboard Statistics Testing
**Login as**: `admin@example.com`
**Expected Results**:
- Projects: 3
- Active Tasks: ~24
- Team Members: 3
- Overdue Tasks: 6

### 2. Empty Dashboard Testing
**Create a new user or use a user with no projects**
**Expected Results**:
- All stats show 0
- "No Projects Yet" message appears
- "Create Your First Project" button visible

### 3. Navigation Testing
**Login as**: Any user
**Test**:
- Click through Dashboard → Projects → Tasks → Team → Settings
- URL should change
- Page content should update
- Active nav item should highlight

### 4. Project Member Permissions
**Login as**: `john@example.com` (Editor)
**Can**:
- View assigned projects
- Edit tasks
- Update task status
- Comment on tasks

**Login as**: `bob@example.com` (Viewer)
**Can Only**:
- View project
- View tasks
- Cannot edit

### 5. Overdue Task Highlighting
**Login as**: `admin@example.com`
**Expected**:
- Overdue card shows 6 tasks
- Red border and styling
- Red icon and text
- Tasks with past due dates in In Progress and Review

---

## Database Reset Commands

```bash
# Full database refresh with all test data
php artisan migrate:fresh --seed

# Or step by step:
php artisan migrate:fresh     # Drop and recreate tables
php artisan db:seed           # Seed all data
```

---

## Additional Test Data

### Roles Created
1. Admin (id: 1)
2. Instructor (id: 2)
3. Student (id: 3)

### Kanban Columns (per board)
1. Backlog (position: 1, WIP limit: 0)
2. To Do (position: 2, WIP limit: 5)
3. In Progress (position: 3, WIP limit: 3)
4. Review (position: 4, WIP limit: 5)
5. Done (position: 5, WIP limit: 0)

### Task Priorities
- Critical (for urgent bugs)
- High (important features)
- Medium (regular work)
- Low (nice-to-have)

---

## Notes for Developers

1. **Password for all users**: `password`
2. **Overdue tasks are intentional**: Set with past due dates for testing alerts
3. **Admin owns all projects**: Other users are only members
4. **Done tasks**: Should NOT appear in "Active Tasks" count
5. **Team Members count**: Shows unique users across all user's projects (excluding owner)

---

## Quick Test Checklist

- [ ] Login with admin@example.com - Dashboard shows stats
- [ ] Login with john@example.com - Can see 2 projects
- [ ] Login with bob@example.com - Limited to viewer role
- [ ] Dashboard stats match expected values
- [ ] Overdue tasks show in red
- [ ] Navigation works between all pages
- [ ] Empty state shows for users with no projects
- [ ] Logout and re-login maintains session

---

**Generated by**: DatabaseSeeder + DashboardTestSeeder
**Seeder Location**: `database/seeders/`
