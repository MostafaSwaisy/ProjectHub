# Feature Specification: User Management System

**Feature Branch**: `006-user-management`
**Created**: 2026-03-10
**Status**: Draft
**Input**: User Management System for ProjectHub - including User Management Dashboard to view and manage all users in the system with search and filtering capabilities; User Profiles & Settings allowing users to edit their profile information, manage preferences, update contact details and change passwords; Task Assignment & Roles system to assign tasks to users, define project roles (Owner, Lead, Member, Viewer), set role-based permissions and control who can assign tasks.

## User Scenarios & Testing *(mandatory)*

<!--
  IMPORTANT: User stories should be PRIORITIZED as user journeys ordered by importance.
  Each user story/journey must be INDEPENDENTLY TESTABLE - meaning if you implement just ONE of them,
  you should still have a viable MVP (Minimum Viable Product) that delivers value.
  
  Assign priorities (P1, P2, P3, etc.) to each story, where P1 is the most critical.
  Think of each story as a standalone slice of functionality that can be:
  - Developed independently
  - Tested independently
  - Deployed independently
  - Demonstrated to users independently
-->

### User Story 1 - View and Manage All Users (Priority: P1)

As a system administrator, I need to see a dashboard listing all users in the ProjectHub system so I can monitor user activity, manage accounts, and maintain system health.

**Why this priority**: This is the foundational feature enabling user management. Without visibility into all users, administrators cannot perform any management tasks effectively.

**Independent Test**: Admin can view a paginated list of all users with name, email, and role filtering working independently. This provides immediate value for system oversight.

**Acceptance Scenarios**:

1. **Given** an admin user is logged in, **When** they navigate to the Users Management page, **Then** they see a paginated list of all users in the system with columns for name, email, role, and status
2. **Given** a list of users is displayed, **When** admin searches for a user by name or email, **Then** results filter in real-time
3. **Given** a list of users is displayed, **When** admin filters by system role (admin, instructor, student), **Then** only users with that role appear
4. **Given** an admin is viewing users, **When** they click on a user, **Then** they can view and edit that user's basic profile information (name, email, system role) but cannot reset their password
5. **Given** an admin views the user list, **When** the list has more than 20 users, **Then** pagination controls appear allowing navigation through pages

---

### User Story 2 - User Profile Management (Priority: P1)

As a user, I need to manage my own profile information including name, email, avatar, and preferences so I can keep my account current and personalized.

**Why this priority**: Users must be able to control their own account information. This is essential for usability and data accuracy.

**Independent Test**: Users can edit and save their profile with changes persisting. Settings page works independently for any user.

**Acceptance Scenarios**:

1. **Given** a logged-in user navigates to their Profile page, **When** they edit their name and save, **Then** the change persists and appears throughout the system
2. **Given** a user in the Profile page, **When** they upload an avatar image, **Then** the image is displayed on their profile and in task assignments
3. **Given** a user in the Profile page, **When** they update email preferences (notification frequency, digest vs. real-time), **Then** settings are saved and honored by the notification system
4. **Given** a user in Profile settings, **When** they change their password, **Then** the old password is invalidated and they must use the new one for future logins
5. **Given** profile form with required fields, **When** a user leaves required fields empty and tries to save, **Then** validation errors are shown

---

### User Story 3 - Task Assignment to Users (Priority: P1)

As a project member, I need to assign tasks to team members so that work is clearly distributed and responsibilities are clear. Members can self-assign tasks; Owner/Lead can assign tasks to anyone.

**Why this priority**: Task assignment is core functionality. Without the ability to assign work to specific people, project management becomes ineffective.

**Independent Test**: Tasks can be assigned to team members with assignees visible on task cards. Assignment changes persist and are reflected immediately.

**Acceptance Scenarios**:

1. **Given** a task in the kanban board, **When** an Owner/Lead clicks the assignee area, **Then** a dropdown appears showing all assignable team members; **When** a Member clicks the assignee area, **Then** the dropdown shows only themselves
2. **Given** an assignee dropdown is open, **When** they select a team member (or self for Members), **Then** that member is assigned to the task and the task card updates immediately
3. **Given** a task has an assignee, **When** the assignee is changed, **Then** the previous assignee is notified and the new assignee is notified
4. **Given** a task card with an assignee, **When** the task is viewed in detail, **Then** the assignee's avatar and name are displayed prominently
5. **Given** a task currently assigned to a user, **When** that user is removed from the project, **Then** the task is unassigned and flagged for reassignment
6. **Given** a project member, **When** they view "My Tasks" section, **Then** they see all tasks assigned to them

---

### User Story 4 - Invite Users to Projects (Priority: P2)

As a project owner/lead, I need to invite external users to join my project by email so that I can bring on team members without requiring them to already have accounts.

**Why this priority**: Project collaboration requires easy onboarding of team members. This enables projects to scale and add members quickly.

**Independent Test**: Project owners can send email invitations that create pending invitations. Invited users can accept/decline independently.

**Acceptance Scenarios**:

1. **Given** a project owner viewing the project Members page, **When** they click "Invite Member", **Then** an invite dialog appears with email input
2. **Given** the invite dialog is open, **When** they enter email(s) and select a role (Lead, Member, Viewer), **Then** an invitation is created and sent
3. **Given** an invitation has been sent, **When** the invited user receives the email, **Then** it contains a clickable link to accept the invitation
4. **Given** an invited user clicks the invitation link, **When** they don't have an account yet, **Then** they are guided to register and automatically added to the project upon registration
5. **Given** an invited user clicks the invitation link, **When** they already have an account, **Then** they are prompted to log in and the invitation is accepted upon login
6. **Given** an inviter can see pending invitations, **When** an invitation hasn't been accepted after 7 days, **Then** the invitation is marked as pending and can be resent
7. **Given** an invited user views an invitation, **When** they click decline, **Then** the invitation is rejected and they are not added to the project

---

### User Story 5 - Define Roles and Permissions (Priority: P2)

As a project owner, I need to define what different roles (Owner, Lead, Member, Viewer) can do so that team members have appropriate access levels and responsibilities.

**Why this priority**: Role-based access control (RBAC) enables secure and organized team collaboration. This ensures team members have the right permissions for their responsibilities.

**Independent Test**: Roles exist with distinct permissions. Permissions can be viewed and modified. New members get correct default permissions based on role.

**Acceptance Scenarios**:

1. **Given** a project owner viewing project settings, **When** they navigate to Roles & Permissions, **Then** they see a table of defined roles (Owner, Lead, Member, Viewer) with their permissions
2. **Given** roles are displayed, **When** an owner views the Lead role, **Then** they can see which actions (create task, edit task, delete task, assign task, invite member, manage roles) are permitted
3. **Given** a project member list, **When** an owner changes a member's role from Member to Lead, **Then** the member immediately gains Lead permissions
4. **Given** different roles exist, **When** a Viewer attempts to create a task, **Then** they are prevented and shown a permission denied message
5. **Given** roles and permissions, **When** a project is created, **Then** default permissions are set: Owner can do all actions, Lead can create/edit/delete/assign tasks, Member can create/edit own tasks and self-assign, Viewer can read-only
6. **Given** permission hierarchy, **When** a Member tries to invite another user, **Then** the action is blocked (only Owner/Lead can invite)

### Edge Cases

- What happens when a user is deleted from the system? (Tasks assigned to them should be handled)
- How does the system handle duplicate email invitations to the same person? (Prevent duplicate invites for same person to same project)
- What if an invited user registers with a different email than the invitation? (Invitation doesn't auto-accept; they must manually join or re-invite)
- How are permissions enforced if a user's role is changed while they have actions in progress? (Changes apply immediately; any in-flight operations respect new permissions)
- What happens to user data when they're soft-deleted? (Profile hidden from user lists, but task history preserved)
- What happens when a project reaches 20 pending invitations? (New invitations are blocked with a clear message; user must cancel or wait for existing invitations to be accepted/declined/expired)
- What happens if the email service is unavailable during invitation? (Invitation record is still created with email_sent=false; inviter can resend later from the pending invitations list)

## Requirements *(mandatory)*

<!--
  ACTION REQUIRED: The content in this section represents placeholders.
  Fill them out with the right functional requirements.
-->

### Functional Requirements

- **FR-001**: System MUST provide an admin/user dashboard listing all users with pagination, search, and filtering capabilities
- **FR-002**: System MUST allow users to view and edit their own profile (name, email, avatar, preferences)
- **FR-003**: System MUST allow users to change their password with validation that new password differs from old
- **FR-004**: System MUST allow users to update notification preferences (frequency, format, delivery method)
- **FR-005**: System MUST support assigning tasks to team members within a project context
- **FR-006**: System MUST display assignee information clearly on task cards and in task detail views
- **FR-007**: System MUST notify assignees when they are assigned to a task
- **FR-008**: System MUST allow reassignment of tasks to different users
- **FR-009**: System MUST handle unassignment when a user is removed from a project
- **FR-010**: System MUST send email invitations to external users for project membership
- **FR-011**: System MUST allow invited users to accept or decline invitations
- **FR-012**: System MUST auto-register users who accept invitations without pre-existing accounts
- **FR-013**: System MUST define four role types: Owner, Lead, Member, Viewer with distinct permission sets
- **FR-014**: System MUST enforce role-based permissions (RBAC) at the project level
- **FR-015**: System MUST allow project owners to modify member roles at any time
- **FR-016**: System MUST prevent users without permissions from accessing protected actions (with clear messaging)
- **FR-017**: System MUST track and log all permission changes and role assignments
- **FR-018**: System MUST display user avatars/profiles with initials or uploaded image as fallback
- **FR-019**: System MUST support soft-delete of users with profile data preserved for audit purposes
- **FR-020**: System MUST prevent duplicate project invitations to the same user for the same project
- **FR-021**: System MUST limit pending invitations to a maximum of 20 per project at any time

### Key Entities

- **User**: Represents a person in the system with name, email, password, avatar, preferences, role_id, and timestamps. Has relationships to projects (through ProjectMember), tasks (as assignee), and activities (audit trail).
- **ProjectMember**: Junction entity connecting User to Project with a role attribute defining that user's permissions within that specific project. Includes invitation status and timestamps.
- **Role**: Defines a permission set with name, description, and collection of permission flags. Standard roles are Owner, Lead, Member, Viewer with different access levels.
- **Invitation**: Represents a pending project membership invitation sent to an email address. Contains project_id, email, role, status (pending/accepted/declined/expired), email_sent (boolean tracking delivery), expiry_date, and created_by.
- **UserPreference**: Stores user-specific settings like notification frequency, avatar_url, bio, and other customization options.

## Success Criteria *(mandatory)*

<!--
  ACTION REQUIRED: Define measurable success criteria.
  These must be technology-agnostic and measurable.
-->

### Measurable Outcomes

- **SC-001**: Users can complete their profile setup and view their profile in under 2 minutes
- **SC-002**: Project owners can invite a team member via email in under 1 minute (including form submission and email send)
- **SC-003**: Invited users can accept an invitation and be added to a project within 30 seconds of clicking the invitation link
- **SC-004**: Assigning a task to a user takes under 10 seconds from task card to confirmation
- **SC-005**: User list loads and displays with filters applied in under 2 seconds for systems with up to 1000 users
- **SC-006**: 95% of permission checks execute without noticeable delay to end users
- **SC-007**: Role changes take effect immediately (within 1 second) across all user sessions
- **SC-008**: 100% of roles and permissions are enforced consistently - no unauthorized access possible
- **SC-009**: At least 90% of invited users accept project invitations within 7 days
- **SC-010**: System handles concurrent task assignments without race conditions or duplicate assignments
- **SC-011**: Notification delivery for task assignments has 99% delivery rate within 30 seconds
- **SC-012**: User satisfaction with profile/settings interface is 4+ out of 5 (measured via post-setup survey)

## Assumptions

- Users have valid email addresses for invitations
- Project structure with Owner/Lead/Member/Viewer roles already exists in the system
- Avatar uploads are limited to common image formats (JPG, PNG) under 5MB
- Password reset and email verification flows already exist
- Task and project structures are already defined
- Soft delete is already implemented (based on codebase review)
- Email service is available for sending invitations and notifications
- Initial roles (Owner, Lead, Member, Viewer) are pre-created in the system

## Dependencies

- **User Model**: Already exists with authentication; needs profile fields extended
- **Project & ProjectMember Models**: Already exist; Invitation model needs to be created
- **Email Service**: Required for sending invitations and notifications
- **Authentication/Sanctum**: Already implemented; used for session management
- **Database Schema**: Needs Invitation table, extended UserPreference table, Role permissions table
- **Frontend Components**: Vue 3 components needed for user list, profile editor, role selector, invite dialog

## Clarifications

### Session 2026-03-10

- Q: Can Members self-assign tasks, or is all task assignment restricted to Owner/Lead? → A: Members can self-assign only; Owner/Lead can assign tasks to anyone.
- Q: Can admins reset another user's password from the user management dashboard? → A: No. Admins can deactivate accounts only; password resets use the existing self-service email flow.
- Q: What is the limit on project invitations to prevent abuse? → A: Maximum 20 pending invitations per project at a time.
- Q: What happens if email delivery fails when sending an invitation? → A: Create the invitation record regardless; mark email as "not sent"; allow the inviter to resend later.

## Out of Scope

- Social login (OAuth/SSO) - future enhancement
- Two-factor authentication - separate feature
- Batch user import from CSV - future enhancement
- Advanced permission customization - start with fixed role sets
- User activity analytics dashboard - separate feature
