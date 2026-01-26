# RBAC Implementation Checklist

This checklist guides you through integrating the RBAC components into your ProjectHub application.

---

## Phase 1: Prerequisites (Complete Before Integration)

- [ ] Verify Laravel installation with `php artisan --version`
- [ ] Confirm database is set up and migrations have run
- [ ] Verify `roles` table exists in database
- [ ] Verify `users` table has `role_id` foreign key column
- [ ] Verify `project_members` table exists with proper relationships
- [ ] Check that all models (User, Project, Task, Board, Column) are properly defined
- [ ] Verify model relationships are correctly set up:
  - [ ] User.role() -> Role
  - [ ] User.ownedProjects() -> Project
  - [ ] User.projects() -> Project (through project_members)
  - [ ] User.assignedTasks() -> Task
  - [ ] Project.instructor() -> User
  - [ ] Project.members() -> User (through project_members)
  - [ ] Task.column() -> Column
  - [ ] Column.board() -> Board
  - [ ] Board.project() -> Project

---

## Phase 2: File Verification (RBAC Components)

- [ ] `app/Http/Middleware/RoleMiddleware.php` exists
  - [ ] File has correct namespace
  - [ ] RoleMiddleware class extends Middleware properly
  - [ ] handle() method accepts roles as variadic parameter
  - [ ] Returns 401 for unauthenticated users
  - [ ] Returns 403 for unauthorized roles
  - [ ] Returns to next() for authorized users

- [ ] `app/Policies/ProjectPolicy.php` exists
  - [ ] File has correct namespace
  - [ ] Implements all required methods: viewAny, view, create, update, delete
  - [ ] Admin check happens first in each method
  - [ ] Ownership check happens second
  - [ ] Membership check happens last (for students)

- [ ] `app/Policies/TaskPolicy.php` exists
  - [ ] File has correct namespace
  - [ ] Implements all required methods: viewAny, view, create, update, delete
  - [ ] Relationship chain works: Task->Column->Board->Project
  - [ ] Assignee permissions work for update/delete

- [ ] `app/Policies/UserPolicy.php` exists
  - [ ] File has correct namespace
  - [ ] Implements all required methods: viewAny, view, viewProfile, create, update, delete
  - [ ] Student can only view self
  - [ ] Instructor can view assigned students
  - [ ] Admin can view all users

- [ ] `app/Providers/AuthServiceProvider.php` exists
  - [ ] File has correct namespace
  - [ ] $policies array maps all three models to their policies
  - [ ] Namespace imports are correct

---

## Phase 3: Middleware Registration

- [ ] Open `app/Http/Kernel.php`
- [ ] Check if `$routeMiddleware` array exists
- [ ] Add RoleMiddleware registration:
  ```php
  protected $routeMiddleware = [
      // ... existing middleware
      'role' => \App\Http\Middleware\RoleMiddleware::class,
  ];
  ```
- [ ] Save file
- [ ] Verify middleware is registered: `php artisan route:list`

---

## Phase 4: Service Provider Registration

- [ ] Open `config/app.php`
- [ ] Check `$providers` array
- [ ] Verify `Illuminate\Foundation\Support\Providers\AuthServiceProvider::class` is registered
- [ ] If creating custom AuthServiceProvider:
  - [ ] Register in $providers array:
    ```php
    App\Providers\AuthServiceProvider::class,
    ```
- [ ] Save file

---

## Phase 5: Database Seeding

- [ ] Create roles (if not already in database):
  ```bash
  php artisan tinker
  ```
  ```php
  > use App\Models\Role;
  > Role::create(['name' => 'admin']);
  > Role::create(['name' => 'instructor']);
  > Role::create(['name' => 'student']);
  > exit
  ```

- [ ] Assign roles to existing users:
  ```bash
  php artisan tinker
  ```
  ```php
  > use App\Models\User;
  > use App\Models\Role;
  > $user = User::first();
  > $user->role_id = Role::where('name', 'admin')->first()->id;
  > $user->save();
  > exit
  ```

- [ ] Verify roles are set:
  ```bash
  php artisan tinker
  ```
  ```php
  > User::with('role')->get();
  > exit
  ```

---

## Phase 6: Controller Integration

### For Each Controller Using Authorization:

- [ ] Import necessary models:
  ```php
  use App\Models\Project;
  use App\Models\Task;
  ```

- [ ] Add authorization checks in store() method:
  ```php
  $this->authorize('create', Project::class);
  ```

- [ ] Add authorization checks in show() method:
  ```php
  $this->authorize('view', $project);
  ```

- [ ] Add authorization checks in update() method:
  ```php
  $this->authorize('update', $project);
  ```

- [ ] Add authorization checks in delete() method:
  ```php
  $this->authorize('delete', $project);
  ```

- [ ] Test each endpoint with appropriate user roles

### Priority Controllers to Update:
- [ ] ProjectController
- [ ] TaskController
- [ ] UserController
- [ ] BoardController
- [ ] ColumnController

---

## Phase 7: Form Request Authorization

### For Each Form Request:

- [ ] Open form request file (e.g., `StoreProjectRequest.php`)
- [ ] Implement authorize() method:
  ```php
  public function authorize(): bool
  {
      return auth()->user()->can('create', Project::class);
  }
  ```
- [ ] Test authorization logic

### Priority Requests to Update:
- [ ] StoreProjectRequest
- [ ] UpdateProjectRequest
- [ ] StoreTaskRequest
- [ ] UpdateTaskRequest
- [ ] UpdateUserRequest

---

## Phase 8: Route Protection (Optional but Recommended)

- [ ] Open `routes/api.php`
- [ ] Add role middleware to admin routes:
  ```php
  Route::middleware('auth:sanctum', 'role:admin')->group(function () {
      // Admin routes
  });
  ```

- [ ] Add role middleware to instructor routes:
  ```php
  Route::middleware('auth:sanctum', 'role:instructor,admin')->group(function () {
      // Instructor and admin routes
  });
  ```

- [ ] Keep general CRUD routes without role middleware (policy checks in controller)
- [ ] Test routes with different user roles

---

## Phase 9: View/Blade Template Updates

- [ ] Find all Blade templates with action buttons (edit, delete, etc.)
- [ ] Wrap with @can/@cannot directives:
  ```blade
  @can('update', $project)
      <button class="btn btn-primary">Edit</button>
  @endcan

  @cannot('delete', $project)
      <p>You don't have permission to delete this project</p>
  @endcannot
  ```

- [ ] Test visibility with different user roles
- [ ] Update all component templates:
  - [ ] Project display/edit templates
  - [ ] Task display/edit templates
  - [ ] User profile templates
  - [ ] Admin dashboard templates

---

## Phase 10: Frontend (Vue/React) Updates

- [ ] Create `resources/js/composables/usePermissions.js`:
  ```javascript
  import { ref } from 'vue';
  import { useAuth } from './useAuth';

  export function usePermissions() {
      const { user } = useAuth();

      const can = (action, model) => {
          if (!user.value) return false;
          // Implement permission logic
      };

      return { can };
  }
  ```

- [ ] Update components to use permission checks:
  ```vue
  <template>
    <div v-if="can('update', project)">
      <button @click="editProject">Edit</button>
    </div>
  </template>
  ```

- [ ] Test permission visibility in frontend components

---

## Phase 11: Testing

- [ ] Run RBAC test suite:
  ```bash
  php artisan test tests/Feature/RBACTest.php
  ```

- [ ] Expected result: All 38 tests pass
- [ ] If any tests fail:
  - [ ] Read test failure message carefully
  - [ ] Check policy implementation
  - [ ] Verify model relationships
  - [ ] Check role names in database

- [ ] Create additional tests for your specific scenarios:
  ```php
  public function test_custom_scenario() {
      $user = User::factory()->create(['role_id' => $roleId]);
      $resource = Model::factory()->create();

      $this->assertTrue($user->can('action', $resource));
  }
  ```

- [ ] Test each controller endpoint manually:
  - [ ] Admin user access
  - [ ] Instructor user access
  - [ ] Student user access
  - [ ] Unauthenticated user (should get 401)

---

## Phase 12: Edge Cases & Security

- [ ] Test user without role assigned:
  - [ ] Should be treated as unauthorized
  - [ ] Should not have access to resources

- [ ] Test role name case sensitivity:
  - [ ] Database should have lowercase role names
  - [ ] Policies check with lowercase
  - [ ] Middleware parameters should match exactly

- [ ] Test project membership logic:
  - [ ] Student added to project should have access
  - [ ] Student removed from project should lose access
  - [ ] Verify ProjectMember table records

- [ ] Test task assignment:
  - [ ] Assignee should be able to update/delete
  - [ ] Non-assignee instructor should still have access
  - [ ] Non-assignee student should not have access

- [ ] Test admin privilege escalation:
  - [ ] Admin should override all other checks
  - [ ] Admin should be able to view/edit/delete anything

---

## Phase 13: Documentation & Handoff

- [ ] Review `RBAC_IMPLEMENTATION.md` for completeness
- [ ] Review `RBAC_USAGE_EXAMPLES.md` for all examples
- [ ] Create team documentation about:
  - [ ] How to add new policies
  - [ ] How to check permissions in code
  - [ ] Common authorization patterns
  - [ ] Troubleshooting guide

- [ ] Add comments to policies explaining authorization logic
- [ ] Create API documentation mentioning authorization requirements

---

## Phase 14: Deployment

- [ ] Create database backup before migration
- [ ] Test in staging environment:
  - [ ] [ ] Run full test suite
  - [ ] [ ] Test all user roles
  - [ ] [ ] Verify all endpoints
  - [ ] [ ] Check error handling

- [ ] Deploy to production:
  - [ ] [ ] Run migrations
  - [ ] [ ] Seed roles if needed
  - [ ] [ ] Deploy code
  - [ ] [ ] Run tests
  - [ ] [ ] Monitor for errors

- [ ] Post-deployment verification:
  - [ ] [ ] Test critical user flows
  - [ ] [ ] Check logs for authorization errors
  - [ ] [ ] Verify all endpoints work
  - [ ] [ ] Get team feedback

---

## Troubleshooting Quick Reference

### Issue: "This action is unauthorized" on every request
**Checklist:**
- [ ] User has role assigned in database
- [ ] Role has correct name (lowercase)
- [ ] Policy method returns true for this user

### Issue: Policy methods not being called
**Checklist:**
- [ ] AuthServiceProvider is registered in config/app.php
- [ ] Policy namespace is correct
- [ ] Model-to-policy mapping is in $policies array
- [ ] Using $this->authorize() or can() method

### Issue: Middleware returning 403 for valid users
**Checklist:**
- [ ] Middleware parameter matches role name exactly
- [ ] User role_id is set in database
- [ ] Role name is lowercase
- [ ] Relationship User.role() is working

### Issue: Task authorization not working
**Checklist:**
- [ ] Relationship chain loads: Task->Column->Board->Project
- [ ] Project relationship returns correct model
- [ ] All foreign keys are set correctly

### Issue: Student can't access project tasks
**Checklist:**
- [ ] Student is added to project_members table
- [ ] project_members.user_id is correct
- [ ] project_members.project_id is correct
- [ ] Policy checks project membership correctly

---

## Sign-Off

- [ ] RBAC implementation complete
- [ ] All tests passing
- [ ] All controllers using authorization
- [ ] All views showing/hiding elements correctly
- [ ] Team trained on RBAC system
- [ ] Documentation complete and shared
- [ ] Deployed to production successfully

---

## Next Steps (After RBAC)

1. **RBAC-007**: Create route guards for role-based navigation (Frontend)
2. **RBAC-008**: Create usePermissions composable (Frontend)
3. **RBAC-009**: Implement conditional UI rendering based on role (Frontend)
4. **AUTH-001-009**: Complete authentication implementation
5. **BOARD-001-004**: Implement board backend
6. **CARD-001-004**: Implement task backend

See `TASKS.md` for complete feature roadmap.

