-- -------------------------------------------------------------
-- Student dashboard demo data seed
-- Populates fees, library, events, attachments and homework so the
-- student/parent dashboard widgets show real data.
-- Target: branch_id = 1, session_id = 4, student enroll_id = 52
-- (class_id = 5, section_id = 1). Safe to run once on empty tables.
-- -------------------------------------------------------------

-- Schema fix: event_types.icon is required by Event::get_events_list
SET @has_icon := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'event_types' AND COLUMN_NAME = 'icon'
);
SET @sql := IF(@has_icon = 0,
    'ALTER TABLE `event_types` ADD COLUMN `icon` varchar(200) NOT NULL DEFAULT '''' AFTER `name`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Event types
INSERT INTO `event_types` (`id`, `name`, `icon`, `branch_id`) VALUES
(1, 'Sports Day', 'fas fa-running', 1),
(2, 'Annual Day', 'fas fa-award', 1),
(3, 'Parent Meeting', 'fas fa-users', 1);

-- Events (2 within the last month -> "Events THIS MONTH" widget)
INSERT INTO `event` (`title`, `remark`, `status`, `type`, `audition`, `selected_list`, `start_date`, `end_date`, `image`, `created_by`, `session_id`, `branch_id`, `show_web`) VALUES
('Inter-House Cricket Match', 'Annual sports day cricket final between houses. All students must reach the ground by 9 AM in sports uniform.', 1, '1', '1', '', '2026-10-15', '2026-10-15', '', 'Johny', 4, 1, 1),
('Diwali Celebration', 'School Diwali celebration with cultural programme. Classes remain suspended after 12 PM.', 1, '2', '1', '', '2026-10-28', '2026-10-28', '', 'Johny', 4, 1, 1),
('Parent Teacher Meeting', 'Quarterly parent teacher meeting for all classes. Parents should bring the student diary.', 1, '3', '1', '', '2026-11-07', '2026-11-07', '', 'Johny', 4, 1, 1),
('Children''s Day', 'Children''s Day celebration and fun activities organised by teachers.', 1, '2', '1', '', '2026-11-14', '2026-11-14', '', 'Johny', 4, 1, 1);

-- Schema fix: fee FKs must reference fees_type (used by the app), not fees_type_old
ALTER TABLE `fee_groups_details` DROP FOREIGN KEY `fee_groups_details_rms_2`;
ALTER TABLE `fee_groups_details` ADD CONSTRAINT `fee_groups_details_rms_2` FOREIGN KEY (`fee_type_id`) REFERENCES `fees_type` (`id`) ON DELETE CASCADE;
ALTER TABLE `fee_payment_history` DROP FOREIGN KEY `fee_payment_history_rms_2`;
ALTER TABLE `fee_payment_history` ADD CONSTRAINT `fee_payment_history_rms_2` FOREIGN KEY (`type_id`) REFERENCES `fees_type` (`id`) ON DELETE CASCADE;

-- Fee group for Class 2 (class_id = 5)
INSERT INTO `fee_groups` (`id`, `name`, `description`, `session_id`, `system`, `branch_id`) VALUES
(1, 'Class 2 Fee Group', 'Standard fee group for Class 2 students', 4, 0, 1);

-- Fee group details (due dates drive the annual fee chart)
INSERT INTO `fee_groups_details` (`fee_groups_id`, `fee_type_id`, `amount`, `due_date`) VALUES
(1, 1, 5000.00, '2026-04-10'),
(1, 4, 3000.00, '2026-04-10'),
(1, 5, 1200.00, '2026-04-10'),
(1, 2, 1500.00, '2026-04-10'),
(1, 2, 1500.00, '2026-05-10'),
(1, 2, 1500.00, '2026-06-10'),
(1, 2, 1500.00, '2026-07-10'),
(1, 2, 1500.00, '2026-08-10'),
(1, 2, 1500.00, '2026-09-10'),
(1, 2, 1500.00, '2026-10-10'),
(1, 2, 1500.00, '2026-11-10'),
(1, 2, 1500.00, '2026-12-10'),
(1, 2, 1500.00, '2027-01-10'),
(1, 2, 1500.00, '2027-02-10'),
(1, 2, 1500.00, '2027-03-10'),
(1, 3, 1000.00, '2026-04-15'),
(1, 3, 1000.00, '2026-08-15'),
(1, 3, 1000.00, '2026-12-15');

-- Allocate the fee group to the student (fee_allocation.student_id = enroll.id)
INSERT INTO `fee_allocation` (`student_id`, `group_id`, `branch_id`, `session_id`, `prev_due`) VALUES
(52, 1, 1, 4, 0.00);

-- Fee payment history (allocation_id 1, type_id = fees_type.id)
-- October payment makes the "Fees Payment THIS MONTH" widget show INR 1500.00
INSERT INTO `fee_payment_history` (`allocation_id`, `type_id`, `transport_fee_details_id`, `collect_by`, `amount`, `discount`, `fine`, `pay_via`, `remarks`, `date`) VALUES
(1, 1, NULL, 'Johny', 5000.00, 0.00, 0.00, '1', 'Admission fee paid', '2026-04-05'),
(1, 4, NULL, 'Johny', 3000.00, 0.00, 0.00, '1', 'Annual charge paid', '2026-04-05'),
(1, 5, NULL, 'Johny', 1200.00, 0.00, 0.00, '1', 'Computer fee paid', '2026-04-05'),
(1, 2, NULL, 'Johny', 1500.00, 0.00, 0.00, '1', 'April tuition fee', '2026-04-05'),
(1, 3, NULL, 'Johny', 1000.00, 0.00, 0.00, '3', 'Term 1 exam fee', '2026-04-12'),
(1, 2, NULL, 'Johny', 1500.00, 0.00, 0.00, '1', 'May tuition fee', '2026-05-06'),
(1, 2, NULL, 'Johny', 1500.00, 0.00, 0.00, '1', 'June tuition fee', '2026-06-05'),
(1, 2, NULL, 'Johny', 1500.00, 0.00, 0.00, '2', 'July tuition fee', '2026-07-07'),
(1, 2, NULL, 'Johny', 1500.00, 0.00, 0.00, '1', 'August tuition fee', '2026-08-06'),
(1, 3, NULL, 'Johny', 1000.00, 0.00, 0.00, '1', 'Term 2 exam fee', '2026-08-12'),
(1, 2, NULL, 'Johny', 1500.00, 0.00, 0.00, '1', 'September tuition fee', '2026-09-05'),
(1, 2, NULL, 'Johny', 1500.00, 0.00, 0.00, '1', 'October tuition fee', '2026-10-08');

-- Library: categories and books
INSERT INTO `book_category` (`id`, `name`, `branch_id`) VALUES
(1, 'Textbooks', 1),
(2, 'Story Books', 1);

INSERT INTO `book` (`title`, `cover`, `author`, `isbn_no`, `category_id`, `publisher`, `edition`, `purchase_date`, `description`, `price`, `total_stock`, `issued_copies`, `branch_id`) VALUES
('Mathematics Class 2', '', 'NCERT', '9788174501010', 1, 'NCERT Publications', '2026', '2026-04-01', 'Mathematics textbook for Class 2 as per CBSE syllabus.', 180.00, '40', '1', 1),
('Hindi Vyakaran Saral', '', 'R.P. Sharma', '9788174501027', 1, 'Saraswati House', '2025', '2026-04-01', 'Hindi grammar book with practice exercises.', 150.00, '35', '1', 1),
('Panchatantra Stories', '', 'Vishnu Sharma', '9788174501034', 2, 'Children Book Trust', '2024', '2026-04-02', 'Classic moral stories from Panchatantra with illustrations.', 220.00, '25', '0', 1),
('My First Science Book', '', 'A. Kumar', '9788174501041', 2, 'Oxford Press', '2025', '2026-04-02', 'Illustrated introduction to basic science for kids.', 260.00, '20', '0', 1);

-- Book issues (this month -> "Book Issued THIS MONTH" widget)
INSERT INTO `book_issues` (`book_id`, `user_id`, `role_id`, `date_of_issue`, `date_of_expiry`, `return_date`, `fine_amount`, `status`, `issued_by`, `return_by`, `session_id`, `branch_id`) VALUES
(1, 52, 7, '2026-10-10', '2026-10-25', NULL, 0.00, 0, 'Johny', NULL, 4, 1),
(2, 52, 7, '2026-10-24', '2026-11-08', NULL, 0.00, 0, 'Johny', NULL, 4, 1);

-- Attachment types and attachments for class_id = 5 (this month)
INSERT INTO `attachments_type` (`id`, `name`, `branch_id`) VALUES
(1, 'Study Material', 1),
(2, 'Notice', 1);

INSERT INTO `attachments` (`title`, `remarks`, `type_id`, `uploader_id`, `class_id`, `file_name`, `enc_name`, `subject_id`, `session_id`, `date`, `branch_id`) VALUES
('Maths Practice Worksheet - Chapter 4', 'Practice questions for Chapter 4: Addition and Subtraction.', 1, '1', '5', 'maths_worksheet_ch4.csv', 'demo_maths_worksheet_ch4.csv', '3', 4, '2026-10-12', 1),
('English Reading Assignment', 'Read the passage and answer the questions in your notebook.', 1, '1', '5', 'english_reading.csv', 'demo_english_reading.csv', '2', 4, '2026-10-20', 1),
('PTM Circular - November', 'Parent Teacher Meeting on 7 November. Timing: 10 AM to 12 PM.', 2, '1', 'unfiltered', 'ptm_circular_nov.csv', 'demo_ptm_circular_nov.csv', 'unfiltered', 4, '2026-10-27', 1);

-- Homework for class_id = 5, section_id = 1 (status 0 = published/live)
INSERT INTO `homework` (`class_id`, `section_id`, `session_id`, `subject_id`, `date_of_homework`, `date_of_submission`, `description`, `created_by`, `create_date`, `status`, `sms_notification`, `schedule_date`, `document`, `evaluation_date`, `evaluated_by`, `branch_id`) VALUES
(5, 1, 4, 3, '2026-10-30', '2026-11-03', 'Solve exercise 4.2 questions 1 to 10 in your maths notebook.', '1', '2026-10-30', '0', 0, NULL, '', NULL, NULL, 1),
(5, 1, 4, 1, '2026-10-31', '2026-11-04', 'Write an essay on "My School" in 150 words and learn poem from page 24.', '1', '2026-10-31', '0', 0, NULL, '', NULL, NULL, 1),
(5, 1, 4, 2, '2026-10-31', '2026-11-05', 'Read chapter 3 and write five new words with meanings.', '1', '2026-10-31', '0', 0, NULL, '', NULL, NULL, 1);
