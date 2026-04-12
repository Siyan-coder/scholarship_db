-- Drop all tables
DROP TABLE IF EXISTS scholarship_applications;
DROP TABLE IF EXISTS students;

-- Create students table
CREATE TABLE students (
  id int(11) NOT NULL AUTO_INCREMENT,
  student_id varchar(50) NOT NULL UNIQUE,
  full_name varchar(100) NOT NULL,
  email varchar(100) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  course varchar(100) NOT NULL,
  year_level varchar(20) NOT NULL,
  contact_number varchar(20) DEFAULT NULL,
  address text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create scholarship_applications table
CREATE TABLE scholarship_applications (
  id int(11) NOT NULL AUTO_INCREMENT,
  full_name varchar(100) NOT NULL,
  student_id varchar(50) NOT NULL,
  email varchar(100) NOT NULL,
  course varchar(100) NOT NULL,
  year_level varchar(20) NOT NULL,
  gpa decimal(3,2) NOT NULL,
  scholarship_type varchar(100) NOT NULL,
  address text DEFAULT NULL,
  contact_number varchar(20) DEFAULT NULL,
  status varchar(20) DEFAULT 'Pending',
  eligibility varchar(20) DEFAULT NULL,
  application_date timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
