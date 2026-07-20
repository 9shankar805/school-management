# Requirements Document

## Introduction

Module 6 of the School Management ERP delivers a dedicated Parent Portal. Parents log in via the existing Laravel authentication system, land on a role-specific dashboard, and can monitor their child's (or children's) academic and administrative status, communicate with teachers, submit leave applications on behalf of their child, and pay outstanding fees online — all without administrative access to any other student's data.

The module resolves the critical data-model gap where the `student_parent_infos` table has no FK columns linking a parent `User` to their child `User` records. It introduces a `parent_student` pivot table and new Eloquent relationships. All new routes are grouped under the existing `auth` middleware and guarded by `@role('parent')` or fine-grained `@can` checks consistent with the Spatie RBAC pattern used throughout the application.

The stack is Laravel 8 / PHP 8.3, Blade + Tailwind CSS, Bootstrap Icons, ApexCharts, and the repository pattern (`app/Interfaces/` + `app/Repositories/`).

---

## Glossary

- **Parent_Portal**: The overall module providing parent-facing views and actions.
- **Parent**: A `User` record holding the Spatie role `parent`.
- **Child**: A `User` record holding the Spatie role `student` that is linked to a Parent via the `parent_student` pivot table.
- **Parent_Student_Link**: A row in the new `parent_student` pivot table connecting a parent `user_id` to a child `user_id`.
- **Attendance_Record**: A row in the `attendances` table for a specific Child.
- **Mark_Record**: A row in the `marks` or `final_marks` table for a specific Child.
- **Invoice**: A row in the `invoices` table where `student_id` equals a Child's `id`.
- **Payment**: A row in the `payments` table linked to an Invoice.
- **Assignment**: A row in the `assignments` table matching a Child's `class_id` and `section_id`.
- **Student_Leave_Application**: A `leave_applications` row where the applicant is a Child and the `submitted_by_parent` flag is `true`.
- **Message**: A row in a new `parent_teacher_messages` table for threaded communication between a Parent and a Teacher.
- **Conversation**: A logical thread of Messages between one Parent and one Teacher scoped to one Child.
- **Notification**: A Laravel database notification dispatched to a Parent's User record.
- **Session**: The current `school_session_id` resolved by the `SchoolSessionTrait`.
- **Performance_Trend**: An ApexCharts line graph showing a Child's average mark per exam over time.

---

## Requirements

### Requirement 1: Parent–Child Account Linkage

**User Story:** As a school administrator, I want to link parent user accounts to specific student accounts, so that parents can only view data for their own children.

#### Acceptance Criteria

1. THE System SHALL store parent-to-child relationships in a `parent_student` pivot table with columns `parent_id` (FK → `users.id`), `student_id` (FK → `users.id`), `relationship` (enum: `father`, `mother`, `guardian`), and `is_primary` (boolean, default `false`).
2. WHEN an administrator creates or edits a student profile, THE System SHALL present a UI control to search for and attach one or more parent User accounts, specifying the relationship type for each.
3. WHEN a parent User account is attached to a student, THE System SHALL validate that the target User holds the `student` role; IF the target User does not hold the `student` role, THEN THE System SHALL reject the linkage and return a validation error.
4. WHEN a student User is attached to a parent User account, THE System SHALL validate that the target User holds the `parent` role; IF the target User does not hold the `parent` role, THEN THE System SHALL reject the linkage and return a validation error.
5. THE User model SHALL expose a `children()` hasMany-through relationship that retrieves all Child User records linked via `parent_student` for a given Parent.
6. THE User model SHALL expose a `parents()` hasMany-through relationship that retrieves all Parent User records linked via `parent_student` for a given Child.
7. WHEN a parent–child link is deleted by an administrator, THE System SHALL remove only that specific `parent_student` row and SHALL NOT delete either User record.
8. THE System SHALL enforce that each `(parent_id, student_id)` combination is unique in the `parent_student` table.

---

### Requirement 2: Parent Dashboard

**User Story:** As a parent, I want a dedicated dashboard that summarises all my children's key information in one place, so that I can quickly assess their academic status.

#### Acceptance Criteria

1. WHEN a User with the `parent` role accesses `/home`, THE Parent_Portal SHALL render the view `dashboards.parent` via `HomeController::parentDashboard()`.
2. THE Parent_Portal SHALL resolve the authenticated parent's children exclusively via the `parent_student` pivot table.
3. WHEN the authenticated parent has no linked children, THE Parent_Portal SHALL display an informational notice with instructions to contact school administration.
4. THE Parent_Portal SHALL display one summary card per Child containing: the child's full name, avatar, current class and section, today's attendance status, attendance percentage for the current Session, count of unpaid Invoices, and the five most recent Mark_Records.
5. WHEN a Child's attendance percentage for the current Session falls below 75%, THE Parent_Portal SHALL display a visual warning indicator on that child's summary card.
6. WHEN the authenticated parent has more than one linked Child, THE Parent_Portal SHALL render all children's summary cards and SHALL provide a child-selector control that scrolls or filters to a specific child.
7. THE Parent_Portal SHALL display school notices from the `notices` table scoped to the current Session.
8. WHEN a Parent has unread Notifications, THE Parent_Portal SHALL display the count of unread notifications in the dashboard header.

---

### Requirement 3: Child Attendance View

**User Story:** As a parent, I want to view my child's full attendance record, so that I can track their school presence and identify absence patterns.

#### Acceptance Criteria

1. WHEN a Parent navigates to the child attendance page for a given Child, THE Parent_Portal SHALL display a monthly calendar view showing each school day marked as `present`, `absent`, or `no record`.
2. THE Parent_Portal SHALL calculate and display the attendance percentage as `(present_count / total_records) * 100`, rounded to one decimal place, for the selected month and for the full current Session.
3. WHEN a Parent selects a different month using the month-navigation control, THE Parent_Portal SHALL reload the calendar view scoped to that month without a full page reload or via a standard GET request with month/year parameters.
4. THE Parent_Portal SHALL display a per-course attendance breakdown table showing course name, total classes, present count, absent count, and attendance percentage per course.
5. WHEN a Child's overall Session attendance percentage falls below 75%, THE Parent_Portal SHALL display a prominent shortage alert with the calculated deficit (i.e., how many more days of attendance are required to reach 75%).
6. IF a Parent attempts to view attendance data for a Child that is not linked to their account, THEN THE System SHALL return a 403 Forbidden response and SHALL NOT render any attendance data.
7. THE Parent_Portal SHALL display an attendance trend chart (ApexCharts area chart) showing the Child's weekly present/absent counts for the last 12 weeks of the current Session.

---

### Requirement 4: Child Exam Results and Marks View

**User Story:** As a parent, I want to view my child's exam results and marks across all subjects, so that I can monitor their academic performance.

#### Acceptance Criteria

1. WHEN a Parent navigates to the child results page, THE Parent_Portal SHALL display all Mark_Records for the selected Child grouped by exam name and ordered by exam date descending.
2. THE Parent_Portal SHALL display for each Mark_Record: the exam name, course name, marks obtained, full marks (from the `exam_rules` table), and the calculated percentage.
3. THE Parent_Portal SHALL display the child's class rank (position by total marks) within the current exam if ranking data is available in the `final_marks` table.
4. WHEN a Parent selects a specific exam from a dropdown filter, THE Parent_Portal SHALL filter the results table to show only marks for that exam.
5. WHEN a Parent selects a specific course from a dropdown filter, THE Parent_Portal SHALL filter the results table to show only marks for that course.
6. IF a Parent attempts to view results for a Child not linked to their account, THEN THE System SHALL return a 403 Forbidden response.
7. THE Parent_Portal SHALL display a Performance_Trend chart (ApexCharts line chart) showing the Child's average marks per exam event ordered chronologically.

---

### Requirement 5: Fee Viewing and Online Payment

**User Story:** As a parent, I want to view my child's fee invoices and pay outstanding amounts online, so that I can manage school payments conveniently.

#### Acceptance Criteria

1. WHEN a Parent navigates to the child fees page, THE Parent_Portal SHALL display all Invoices for the selected Child, showing: invoice title, amount, due date, status (`paid` / `unpaid` / `overdue`), and any partial payments made.
2. THE Parent_Portal SHALL mark an Invoice as `overdue` in the display when its `due_date` is before today and its `status` is `unpaid`.
3. WHEN a Parent clicks "Pay Now" on an unpaid Invoice, THE Parent_Portal SHALL redirect the Parent to a secure payment page scoped to that Invoice.
4. THE Payment page SHALL accept the payment method selection (cash/online placeholder) and SHALL record a new Payment row with `invoice_id`, `amount_paid`, `payment_date` set to today, and `payment_method`.
5. WHEN a Payment is successfully recorded and the `amount_paid` equals the Invoice `amount`, THE System SHALL update the Invoice `status` to `paid`.
6. WHEN a Payment is successfully recorded and the `amount_paid` is less than the Invoice `amount`, THE System SHALL leave the Invoice `status` as `unpaid` and SHALL display the remaining balance on the fees page.
7. THE Parent_Portal SHALL display a payment history table showing all past Payments for each child, including invoice title, amount paid, payment date, and payment method.
8. THE Parent_Portal SHALL display a total outstanding balance (sum of unpaid Invoice amounts) per Child in the fees summary card.
9. IF a Parent attempts to pay an Invoice belonging to a Child not linked to their account, THEN THE System SHALL return a 403 Forbidden response and SHALL NOT process the payment.
10. WHEN a Payment is successfully recorded, THE System SHALL dispatch a `FeeReminderNotification` (or equivalent receipt notification) to the Parent's User account.

---

### Requirement 6: Homework and Assignments View

**User Story:** As a parent, I want to view the homework and assignments posted for my child's class, so that I can support their studies at home.

#### Acceptance Criteria

1. WHEN a Parent navigates to the child assignments page, THE Parent_Portal SHALL display all Assignment records where the `class_id` and `section_id` match the Child's current academic class and section, scoped to the current Session.
2. THE Parent_Portal SHALL display for each Assignment: the assignment name, course name, teacher name, date posted, and a download link for the assignment file if `assignment_file_path` is set.
3. THE Parent_Portal SHALL sort assignments by creation date descending, with the most recent appearing first.
4. WHEN a Parent clicks the assignment file download link, THE Parent_Portal SHALL serve the file from storage using the `file.serve` named route, restricted to authorised users only.
5. WHEN a Child has no current class/section assignment in the current Session, THE Parent_Portal SHALL display an informational message indicating the child's class information is not yet configured.
6. IF a Parent attempts to view assignments for a Child not linked to their account, THEN THE System SHALL return a 403 Forbidden response.

---

### Requirement 7: Leave Application for Child

**User Story:** As a parent, I want to submit a leave application on behalf of my child, so that the school is informed of planned absences.

#### Acceptance Criteria

1. WHEN a Parent submits a leave application form for a Child, THE System SHALL create a `leave_applications` row with `user_id` set to the Child's id, `submitted_by` set to the Parent's id, `leave_type_id`, `from_date`, `to_date`, `reason`, and `status` set to `pending`.
2. THE leave application form SHALL include fields for: leave type (populated from active `leave_types`), from date, to date, and reason (text, minimum 10 characters).
3. WHEN the from date is after the to date, THE System SHALL reject the form and return a validation error message.
4. WHEN the from date is more than 90 days in the past, THE System SHALL reject the form and return a validation error message.
5. THE Parent_Portal SHALL display a list of all previously submitted leave applications for each Child, showing: leave type, from date, to date, total days, status (pending/approved/rejected/cancelled), and any reviewer notes.
6. WHEN a leave application has status `pending`, THE Parent_Portal SHALL display a "Cancel" button that sets the status to `cancelled` when clicked.
7. WHEN a leave application status is `approved` or `rejected`, THE Parent_Portal SHALL NOT display a "Cancel" button for that application.
8. IF a Parent attempts to submit or cancel a leave application for a Child not linked to their account, THEN THE System SHALL return a 403 Forbidden response.
9. WHEN a leave application is successfully submitted, THE System SHALL dispatch a database Notification to users holding the `class-teacher` role assigned to the Child's section, informing them of the pending leave request.

---

### Requirement 8: Teacher–Parent Messaging

**User Story:** As a parent, I want to send and receive messages with my child's teachers, so that we can communicate about my child's progress and any concerns.

#### Acceptance Criteria

1. THE System SHALL store messages in a `parent_teacher_messages` table with columns: `id`, `conversation_id` (FK → `conversations.id`), `sender_id` (FK → `users.id`), `body` (text), `read_at` (nullable timestamp), `created_at`, `updated_at`.
2. THE System SHALL store conversations in a `conversations` table with columns: `id`, `parent_id` (FK → `users.id`), `teacher_id` (FK → `users.id`), `student_id` (FK → `users.id`), `subject` (string, max 150 characters), `created_at`, `updated_at`.
3. WHEN a Parent initiates a new conversation, THE Parent_Portal SHALL display a form with: teacher selector (populated with teachers assigned to the Child's class/section via `assigned_teachers`), child selector (from linked children), and a subject field.
4. WHEN a Parent sends a message within a Conversation, THE System SHALL create a new Message row and SHALL dispatch a database Notification to the recipient Teacher.
5. WHEN a Teacher sends a reply within a Conversation, THE System SHALL create a new Message row and SHALL dispatch a database Notification to the Parent.
6. THE Parent_Portal SHALL display all Conversations for the authenticated Parent, ordered by the most recent Message timestamp descending.
7. THE Parent_Portal SHALL display the Message thread when a Conversation is selected, showing all messages in chronological order.
8. WHEN a Parent views a Conversation thread, THE System SHALL set `read_at` to the current timestamp on all Messages in that thread where `sender_id` is not the Parent's id and `read_at` IS NULL.
9. THE Parent_Portal SHALL display an unread message badge count in the messaging navigation link equal to the number of Messages addressed to the Parent where `read_at` IS NULL.
10. IF a Parent attempts to read or send messages in a Conversation where `parent_id` does not match the authenticated Parent's id, THEN THE System SHALL return a 403 Forbidden response.
11. THE System SHALL enforce that Message `body` is between 1 and 2000 characters; IF the body fails validation, THEN THE System SHALL return a validation error without saving the message.

---

### Requirement 9: Performance Trend Graphs

**User Story:** As a parent, I want to see visual charts of my child's academic performance over time, so that I can identify improvement or decline trends.

#### Acceptance Criteria

1. WHEN a Parent views the performance trends page for a Child, THE Parent_Portal SHALL render an ApexCharts line chart showing the Child's average marks per exam event ordered chronologically for the current Session.
2. THE Parent_Portal SHALL render an ApexCharts bar chart showing the Child's average marks per course for the current Session.
3. WHEN the Child has marks for more than one Session available, THE Parent_Portal SHALL render a multi-series line chart comparing average marks per exam across Sessions.
4. WHEN a Child has fewer than two Mark_Records in the current Session, THE Parent_Portal SHALL display an informational message stating insufficient data for trend analysis instead of an empty chart.
5. THE performance charts SHALL use the ApexCharts library already loaded in the application layout and SHALL NOT introduce new charting dependencies.
6. IF a Parent attempts to view performance data for a Child not linked to their account, THEN THE System SHALL return a 403 Forbidden response.

---

### Requirement 10: Push and In-App Notifications

**User Story:** As a parent, I want to receive notifications about my child's attendance, fees, and results, so that I stay informed without having to manually check the portal.

#### Acceptance Criteria

1. WHEN a Child is marked `absent` on any given day, THE System SHALL dispatch an `AttendanceAlertNotification` to all Parents linked to that Child via the `parent_student` table.
2. WHEN an Invoice is created for a Child, THE System SHALL dispatch a `FeeReminderNotification` to all Parents linked to that Child.
3. WHEN an Invoice's `due_date` is exactly 3 days away and the Invoice `status` is `unpaid`, THE System SHALL dispatch a `FeeReminderNotification` to all Parents linked to that Child.
4. WHEN new Mark_Records are published for a Child (i.e., a `final_marks` row is inserted or updated to `submitted` status), THE System SHALL dispatch a database Notification to all Parents linked to that Child.
5. THE Parent_Portal SHALL display all Notifications for the authenticated Parent in the existing notification centre view (`notifications.index`) and SHALL support marking individual notifications as read.
6. WHEN a Parent marks a notification as read, THE System SHALL set `read_at` to the current timestamp on that notification row.
7. THE notification bell in the dashboard header SHALL display a badge with the count of unread Notifications for the authenticated Parent.

---

### Requirement 11: Multi-Child Support

**User Story:** As a parent with more than one child enrolled in the school, I want to switch between children's profiles within the portal, so that I can manage each child's information independently.

#### Acceptance Criteria

1. WHEN the authenticated Parent has two or more linked Children, THE Parent_Portal SHALL display a persistent child-selector control (dropdown or tab strip) visible on all parent-specific pages.
2. WHEN a Parent selects a different Child from the child-selector control, THE Parent_Portal SHALL reload the current page scoped to the newly selected Child, passing the child's `id` as a route parameter or query parameter.
3. THE Parent_Portal SHALL validate on every parent-facing request that the `student_id` in the request corresponds to a Child linked to the authenticated Parent; IF the validation fails, THEN THE System SHALL return a 403 Forbidden response.
4. WHEN the authenticated Parent has exactly one linked Child, THE Parent_Portal SHALL auto-select that Child and SHALL NOT display the child-selector control.
5. THE Parent_Portal dashboard SHALL aggregate outstanding invoice totals and attendance summaries across ALL linked Children, with a per-child breakdown visible below the aggregate summary.

---

### Requirement 12: Permissions and Access Control

**User Story:** As a school administrator, I want parent-specific permissions seeded and enforced on all parent portal routes, so that parents cannot access staff or administrative data.

#### Acceptance Criteria

1. THE System SHALL seed the following permissions into the `permissions` table: `view parent portal`, `view child attendance`, `view child results`, `view child fees`, `pay child fees`, `view child assignments`, `apply leave for child`, `message teachers`, `view child notifications`.
2. THE System SHALL assign all nine permissions listed in criterion 1 to the `parent` role in the `RoleSeeder`.
3. WHEN a User without the `parent` role accesses any route under the `/parent` prefix, THE System SHALL redirect to the home route with a 403 response.
4. THE System SHALL protect every parent portal controller action with the appropriate `can` middleware or `$this->authorize()` call corresponding to the permissions defined in criterion 1.
5. WHEN a User with the `parent` role attempts to access an administrative route (e.g., student list, teacher list, mark entry), THE System SHALL return a 403 Forbidden response consistent with the existing Spatie middleware behaviour.

