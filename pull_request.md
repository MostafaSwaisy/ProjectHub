## PR Title: Feat: Implement Modern UI, Dashboard, Kanban, and Navigation
 
 ### Summary
 
 This pull request introduces a massive overhaul of the application's frontend, delivering a modern, feature-rich, and user-friendly experience. This includes a complete redesign of the UI, a new dashboard with statistics, a full-featured Kanban board, and a new navigation system.
 
 ### Key Features
 
 **1. Modern UI & Design System:**
 - **New Design System:** Implemented a foundational design system with CSS variables for a consistent look and feel, including a dark theme, typography, and spacing.
 - **Reusable Components:** Created a suite of reusable components, including buttons, modals, inputs, and dropdowns, to ensure UI consistency.
 - **Animations:** Added a rich set of animations and transitions to enhance the user experience.
 
 **2. Dashboard & Navigation:**
 - **New Dashboard:** A new dashboard page that provides users with key statistics, such as the number of projects, active tasks, team members, and overdue tasks.
 - **Application Layout:** A new `AppLayout` component that includes a responsive sidebar for navigation and a top navbar with a user menu.
 - **Navigation System:** A complete navigation system that allows users to seamlessly move between different sections of the application.
 
 **3. Kanban Board:**
 - **Full-Featured Board:** A complete Kanban board with drag-and-drop functionality for moving tasks between columns.
 - **Task Management:** Users can create, edit, and delete tasks directly from the Kanban board.
 - **Filtering and Sorting:** The board includes options for filtering tasks by various criteria and sorting them.
 - **Responsive Design:** The Kanban board is fully responsive and works seamlessly on mobile and tablet devices.
 
 **4. Modernized Authentication:**
 - **Redesigned Pages:** The login, registration, and password reset pages have been completely redesigned with a modern, glassmorphic UI.
 - **Improved UX:** The new auth pages provide a much-improved user experience with features like a password strength indicator and error animations.
 
 ### Backend Changes
 
 - **Dashboard API:** A new `DashboardController` has been added to provide the statistical data for the new dashboard. The API endpoint is `/api/dashboard/stats`.
 - **Test Data Seeder:** A `DashboardTestSeeder` has been created to populate the database with a comprehensive set of test data, making it easier to test the new features.
 - **API Resources:** The `LoginController` and `RegisterController` now use a `UserResource` to ensure consistent API responses. A new `ProjectResource` has been added as well.
 - **Model Updates:** The `Project` model has been updated with attribute accessors to map database enum values to more user-friendly strings for the API.
 
 ### Bug Fixes
 
 This pull request also includes a number of important bug fixes, including:
 - **Authentication Persistence:** Resolved an issue where users were not staying logged in after a page refresh.
 - **Navigation Rendering:** Fixed a problem where the page content would not update when navigating between routes.
 - **Styling and Layout:** Addressed various styling and layout issues throughout the application.
 
 ### How to Test
 
 1. **Run Migrations and Seed Data:**
 ```bash
 php artisan migrate:fresh --seed
 ```
 2. **Log In:**
 Use one of the test user accounts (e.g., `admin@example.com` / `password`).
 3. **Explore:**
 - Check out the new dashboard and its statistics.
 - Navigate through the application using the new sidebar.
 - Go to a project's Kanban board to test the drag-and-drop functionality and task management features.
 - View the redesigned login and registration pages.