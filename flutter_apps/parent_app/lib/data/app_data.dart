import 'package:flutter/material.dart';

// ---------------------------------------------------------------------------
// 1. CHILD & STUDENT PROFILE
// ---------------------------------------------------------------------------
class Child {
  final int? id;
  final String name, firstName, lastName, grade, section, avatar, photo, schoolName;
  final String rollNo, admissionNo, bloodGroup, birthday, gender, email, phone;
  final double gpa, attendance;
  final int colorHex, rank;
  final Map<String, dynamic>? academicInfo;

  const Child({
    this.id,
    required this.name,
    this.firstName = '',
    this.lastName = '',
    required this.grade,
    required this.section,
    required this.avatar,
    this.photo = '',
    required this.schoolName,
    this.rollNo = '',
    this.admissionNo = '',
    this.bloodGroup = 'O+',
    this.birthday = '2012-05-14',
    this.gender = 'Male',
    this.email = '',
    this.phone = '',
    required this.gpa,
    required this.attendance,
    required this.colorHex,
    required this.rank,
    this.academicInfo,
  });

  factory Child.fromJson(Map<String, dynamic> json) {
    String extractName() {
      if (json['name'] != null && json['name'].toString().isNotEmpty) return json['name'].toString();
      if (json['full_name'] != null && json['full_name'].toString().isNotEmpty) return json['full_name'].toString();
      if (json['student_name'] != null && json['student_name'].toString().isNotEmpty) return json['student_name'].toString();
      if (json['student'] is Map) {
        final s = json['student'];
        if (s['name'] != null && s['name'].toString().isNotEmpty) return s['name'].toString();
        if (s['full_name'] != null && s['full_name'].toString().isNotEmpty) return s['full_name'].toString();
      }
      if (json['user'] is Map) {
        final u = json['user'];
        if (u['name'] != null && u['name'].toString().isNotEmpty) return u['name'].toString();
      }
      final first = json['first_name']?.toString() ?? '';
      final last = json['last_name']?.toString() ?? '';
      final combined = '$first $last'.trim();
      return combined.isNotEmpty ? combined : 'Student';
    }

    String extractGrade() {
      if (json['academic_info'] is Map && json['academic_info']['class_name'] != null) {
        return json['academic_info']['class_name'].toString();
      }
      if (json['grade'] is Map) return (json['grade']['name'] ?? json['grade']['title'] ?? '').toString();
      if (json['class'] is Map) return (json['class']['name'] ?? json['class']['title'] ?? '').toString();
      if (json['student'] is Map && json['student']['class'] != null) {
        final c = json['student']['class'];
        if (c is Map) return (c['name'] ?? c['title'] ?? '').toString();
        return c.toString();
      }
      if (json['grade_name'] != null) return json['grade_name'].toString();
      if (json['class_name'] != null) return json['class_name'].toString();
      if (json['grade'] != null) return json['grade'].toString();
      if (json['class'] != null) return json['class'].toString();
      return 'Grade 8';
    }

    String extractSection() {
      if (json['academic_info'] is Map && json['academic_info']['section_name'] != null) {
        return json['academic_info']['section_name'].toString();
      }
      if (json['section'] is Map) return (json['section']['name'] ?? json['section']['title'] ?? '').toString();
      if (json['student'] is Map && json['student']['section'] != null) {
        final sec = json['student']['section'];
        if (sec is Map) return (sec['name'] ?? sec['title'] ?? '').toString();
        return sec.toString();
      }
      if (json['section_name'] != null) return json['section_name'].toString();
      if (json['section'] != null) return json['section'].toString();
      return 'Section A';
    }

    String extractSchool() {
      if (json['school'] is Map) return (json['school']['name'] ?? json['school']['title'] ?? '').toString();
      if (json['school_name'] != null) return json['school_name'].toString();
      if (json['school'] != null) return json['school'].toString();
      return 'Nepal Model Secondary School';
    }

    final name = extractName();
    final grade = extractGrade();
    final section = extractSection();
    final school = extractSchool();
    final photo = json['photo']?.toString() ?? json['avatar']?.toString() ?? '';

    return Child(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      name: name,
      firstName: json['first_name']?.toString() ?? name.split(' ').first,
      lastName: json['last_name']?.toString() ?? (name.split(' ').length > 1 ? name.split(' ').last : ''),
      grade: grade.isNotEmpty ? (grade.toLowerCase().startsWith('grade') || grade.toLowerCase().startsWith('class') ? grade : 'Grade $grade') : 'Grade 8',
      section: section.isNotEmpty ? (section.toLowerCase().startsWith('section') ? section : 'Section $section') : 'Section A',
      avatar: photo.isNotEmpty ? photo : (name.isNotEmpty ? name[0].toUpperCase() : 'S'),
      photo: photo,
      schoolName: school,
      rollNo: json['roll_no']?.toString() ?? json['roll_number']?.toString() ?? '14',
      admissionNo: json['admission_no']?.toString() ?? 'ADM-2026-089',
      bloodGroup: json['blood_type']?.toString() ?? json['blood_group']?.toString() ?? 'B+ positive',
      birthday: json['birthday']?.toString() ?? json['dob']?.toString() ?? '2012-05-14',
      gender: json['gender']?.toString() ?? 'Male',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      gpa: (json['gpa'] != null) ? (double.tryParse(json['gpa'].toString()) ?? 3.8) : 3.8,
      attendance: (json['attendance_percentage'] != null)
          ? ((double.tryParse(json['attendance_percentage'].toString()) ?? 92.0) / 100.0)
          : (json['attendance'] != null ? (double.tryParse(json['attendance'].toString()) ?? 0.92) : 0.92),
      colorHex: json['color_hex'] is int ? json['color_hex'] : 0xFF6C3CE9,
      rank: json['rank'] is int ? json['rank'] : (int.tryParse(json['rank']?.toString() ?? '') ?? 3),
      academicInfo: json['academic_info'] is Map<String, dynamic> ? json['academic_info'] : null,
    );
  }
}

// ---------------------------------------------------------------------------
// 2. ACADEMICS & REPORT CARDS
// ---------------------------------------------------------------------------
class SubjectGrade {
  final int? id;
  final String name, code, grade, teacher;
  final double score, maxScore, progress;
  final String remarks;
  final int colorHex, iconCode;

  const SubjectGrade({
    this.id,
    required this.name,
    this.code = '',
    required this.grade,
    required this.teacher,
    this.score = 88.0,
    this.maxScore = 100.0,
    required this.progress,
    this.remarks = 'Outstanding performance',
    required this.colorHex,
    required this.iconCode,
  });

  factory SubjectGrade.fromJson(Map<String, dynamic> json) {
    final sc = (json['score'] != null)
        ? (double.tryParse(json['score'].toString()) ?? 85.0)
        : (json['marks'] != null ? (double.tryParse(json['marks'].toString()) ?? 85.0) : 85.0);
    final maxSc = (json['max_score'] != null)
        ? (double.tryParse(json['max_score'].toString()) ?? 100.0)
        : 100.0;
    
    return SubjectGrade(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      name: json['name'] ?? json['subject_name'] ?? json['subject']?['name'] ?? 'Mathematics',
      code: json['code'] ?? json['subject_code'] ?? 'MTH101',
      grade: json['grade'] ?? json['marks_grade'] ?? json['letter_grade'] ?? 'A',
      teacher: json['teacher']?['name'] ?? json['teacher_name'] ?? 'Prof. Sharma',
      score: sc,
      maxScore: maxSc,
      progress: (maxSc > 0) ? (sc / maxSc).clamp(0.0, 1.0) : 0.85,
      remarks: json['remarks'] ?? 'Consistent effort and high participation.',
      colorHex: json['color_hex'] ?? 0xFF6C3CE9,
      iconCode: json['icon_code'] ?? 0xe559,
    );
  }
}

class ReportCard {
  final int? id;
  final String term, academicYear, issueDate, overallGrade, principalRemarks;
  final double gpa, percentage;
  final int totalMarks, obtainedMarks, rank, totalStudents;
  final List<SubjectGrade> subjectGrades;

  const ReportCard({
    this.id,
    required this.term,
    required this.academicYear,
    required this.issueDate,
    required this.overallGrade,
    required this.principalRemarks,
    required this.gpa,
    required this.percentage,
    required this.totalMarks,
    required this.obtainedMarks,
    required this.rank,
    required this.totalStudents,
    required this.subjectGrades,
  });

  factory ReportCard.fromJson(Map<String, dynamic> json) {
    List<SubjectGrade> list = [];
    if (json['subjects'] is List) {
      list = (json['subjects'] as List).map((s) => SubjectGrade.fromJson(s)).toList();
    }
    return ReportCard(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      term: json['term'] ?? json['exam_name'] ?? 'Second Terminal Examination',
      academicYear: json['academic_year'] ?? '2081-2082 (2026)',
      issueDate: json['issue_date'] ?? json['created_at'] ?? '2026-08-15',
      overallGrade: json['overall_grade'] ?? json['grade'] ?? 'A+',
      principalRemarks: json['principal_remarks'] ?? 'Excellent academic progress with commendable discipline.',
      gpa: (json['gpa'] != null) ? (double.tryParse(json['gpa'].toString()) ?? 3.9) : 3.9,
      percentage: (json['percentage'] != null) ? (double.tryParse(json['percentage'].toString()) ?? 91.5) : 91.5,
      totalMarks: json['total_marks'] ?? 700,
      obtainedMarks: json['obtained_marks'] ?? 641,
      rank: json['rank'] ?? 2,
      totalStudents: json['total_students'] ?? 42,
      subjectGrades: list,
    );
  }
}

// ---------------------------------------------------------------------------
// 3. ATTENDANCE
// ---------------------------------------------------------------------------
class AttendanceRecord {
  final int? id;
  final String subject, childName, date, status, checkInTime, checkOutTime, remarks;
  final int present, total, colorHex;

  const AttendanceRecord({
    this.id,
    required this.subject,
    required this.childName,
    this.date = 'Today',
    this.status = 'Present',
    this.checkInTime = '08:45 AM',
    this.checkOutTime = '03:30 PM',
    this.remarks = 'On time',
    required this.present,
    required this.total,
    required this.colorHex,
  });

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    return AttendanceRecord(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      subject: json['subject']?['name'] ?? json['subject'] ?? json['course']?['name'] ?? 'General Attendance',
      childName: json['child_name'] ?? json['student']?['name'] ?? 'Student',
      date: json['date'] ?? json['created_at'] ?? 'Today',
      status: json['status'] ?? 'Present',
      checkInTime: json['check_in_time'] ?? json['time_in'] ?? '08:45 AM',
      checkOutTime: json['check_out_time'] ?? json['time_out'] ?? '03:30 PM',
      remarks: json['remarks'] ?? 'Present via RFID Gate Terminal',
      present: json['present_days'] ?? json['present'] ?? 22,
      total: json['total_days'] ?? json['total'] ?? 24,
      colorHex: json['color_hex'] ?? 0xFF05C46B,
    );
  }
}

// ---------------------------------------------------------------------------
// 4. ASSIGNMENTS & HOMEWORK
// ---------------------------------------------------------------------------
class Assignment {
  final int? id;
  final String title, subject, description, dueDate, assignedDate, priority, childName, status;
  final bool isCompleted;
  final int colorHex;
  final double? maxMarks, score;
  final String? attachmentUrl;

  const Assignment({
    this.id,
    required this.title,
    required this.subject,
    this.description = '',
    required this.dueDate,
    this.assignedDate = 'Yesterday',
    required this.isCompleted,
    required this.priority,
    required this.colorHex,
    required this.childName,
    this.status = 'pending',
    this.maxMarks,
    this.score,
    this.attachmentUrl,
  });

  factory Assignment.fromJson(Map<String, dynamic> json) {
    final comp = json['is_completed'] == true || json['status'] == 'completed' || json['status'] == 'submitted';
    return Assignment(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      title: json['title'] ?? json['name'] ?? 'Chapter Homework',
      subject: json['subject']?['name'] ?? json['course']?['name'] ?? json['subject'] ?? 'General Studies',
      description: json['description'] ?? json['instruction'] ?? 'Complete exercise questions 1 to 10 with step-by-step solutions.',
      dueDate: json['due_date'] ?? json['dueDate'] ?? 'Tomorrow, 5:00 PM',
      assignedDate: json['created_at'] ?? json['assigned_date'] ?? 'Yesterday',
      isCompleted: comp,
      priority: json['priority'] ?? 'medium',
      colorHex: json['color_hex'] ?? 0xFF6C3CE9,
      childName: json['child_name'] ?? json['child']?['name'] ?? 'Student',
      status: json['status'] ?? (comp ? 'completed' : 'pending'),
      maxMarks: (json['max_marks'] != null) ? double.tryParse(json['max_marks'].toString()) : 20.0,
      score: (json['score'] != null) ? double.tryParse(json['score'].toString()) : null,
      attachmentUrl: json['attachment'] ?? json['file_url'],
    );
  }
}

// ---------------------------------------------------------------------------
// 5. EXAMS & ASSESSMENTS
// ---------------------------------------------------------------------------
class ExamItem {
  final int? id;
  final String subject, title, date, time, duration, room, type, childName, syllabus;
  final int daysLeft, totalMarks, passMarks, colorHex;

  const ExamItem({
    this.id,
    required this.subject,
    this.title = '',
    required this.date,
    required this.time,
    this.duration = '2 Hours',
    required this.room,
    required this.type,
    this.syllabus = 'Chapters 1 to 6 comprehensive',
    required this.daysLeft,
    this.totalMarks = 100,
    this.passMarks = 40,
    required this.colorHex,
    required this.childName,
  });

  factory ExamItem.fromJson(Map<String, dynamic> json) {
    return ExamItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      subject: json['subject']?['name'] ?? json['course']?['name'] ?? json['subject'] ?? 'Final Examination',
      title: json['title'] ?? json['name'] ?? 'Term Assessment',
      date: json['date'] ?? json['start_date'] ?? json['exam_date'] ?? '2026-09-15',
      time: json['time'] ?? json['start_time'] ?? '09:00 AM - 11:30 AM',
      duration: json['duration'] ?? '2h 30m',
      room: json['room'] ?? json['hall'] ?? 'Block B - Room 204',
      type: json['type'] ?? json['exam_type'] ?? 'Term Final',
      syllabus: json['syllabus'] ?? 'Complete First Term Coursebook',
      daysLeft: json['days_left'] is int ? json['days_left'] : (int.tryParse(json['days_left']?.toString() ?? '') ?? 4),
      totalMarks: json['total_marks'] ?? 100,
      passMarks: json['pass_marks'] ?? 40,
      colorHex: json['color_hex'] ?? 0xFFFF9F1A,
      childName: json['child_name'] ?? json['student']?['name'] ?? 'Student',
    );
  }
}

// ---------------------------------------------------------------------------
// 6. FEES & INVOICES
// ---------------------------------------------------------------------------
class FeeRecord {
  final int? id;
  final String title, invoiceNumber, dueDate, paidDate, status, paymentMethod;
  final double amount, paidAmount, discount;
  final int colorHex;

  const FeeRecord({
    this.id,
    required this.title,
    this.invoiceNumber = 'INV-2026-0042',
    required this.dueDate,
    this.paidDate = '',
    required this.status,
    this.paymentMethod = 'Online / eSewa',
    required this.amount,
    this.paidAmount = 0.0,
    this.discount = 0.0,
    required this.colorHex,
  });

  double get dueAmount => (amount - paidAmount - discount).clamp(0.0, double.infinity);

  factory FeeRecord.fromJson(Map<String, dynamic> json) {
    final amt = (json['amount'] != null) ? (double.tryParse(json['amount'].toString()) ?? 0.0) : 0.0;
    final paid = (json['paid_amount'] != null) ? (double.tryParse(json['paid_amount'].toString()) ?? 0.0) : 0.0;
    final stat = json['status'] ?? (paid >= amt && amt > 0 ? 'paid' : 'pending');

    return FeeRecord(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      title: json['title'] ?? json['fee_type'] ?? json['name'] ?? 'Monthly Tuition & Transport Fee',
      invoiceNumber: json['invoice_no'] ?? json['invoice_number'] ?? 'INV-2026-${json['id'] ?? '101'}',
      dueDate: json['due_date'] ?? 'End of Current Month',
      paidDate: json['paid_at'] ?? json['payment_date'] ?? '',
      status: stat,
      paymentMethod: json['payment_method'] ?? 'Khalti / eSewa / FonePay',
      amount: amt,
      paidAmount: paid,
      discount: (json['discount'] != null) ? (double.tryParse(json['discount'].toString()) ?? 0.0) : 0.0,
      colorHex: json['color_hex'] ?? 0xFF6C3CE9,
    );
  }
}

// ---------------------------------------------------------------------------
// 7. BUS & GPS TRACKING
// ---------------------------------------------------------------------------
class BusStopItem {
  final String name, arrivalTime, status;
  final bool isCompleted, isCurrent;

  const BusStopItem({
    required this.name,
    required this.arrivalTime,
    required this.status,
    this.isCompleted = false,
    this.isCurrent = false,
  });
}

class BusInfo {
  final int? id;
  final String busNumber, plateNumber, driverName, driverPhone, helperName, helperPhone;
  final String route, status, eta, currentStop, nextStop;
  final double lat, lng, speedKmh;
  final List<BusStopItem> stops;

  const BusInfo({
    this.id,
    required this.busNumber,
    this.plateNumber = 'BA 2 KHA 8492',
    required this.driverName,
    required this.driverPhone,
    this.helperName = 'Ram Bahadur',
    this.helperPhone = '+977 9801234568',
    required this.route,
    required this.status,
    required this.eta,
    this.currentStop = 'Koteshwor Chowk',
    this.nextStop = 'Baneshwor Terminal (Your Stop)',
    required this.lat,
    required this.lng,
    this.speedKmh = 36.5,
    this.stops = const [],
  });

  factory BusInfo.fromJson(Map<String, dynamic> json) {
    List<BusStopItem> stopItems = [];
    if (json['stops'] is List) {
      stopItems = (json['stops'] as List).map((s) => BusStopItem(
        name: s['name'] ?? s['stop_name'] ?? 'Bus Stop',
        arrivalTime: s['time'] ?? s['eta'] ?? '08:15 AM',
        status: s['status'] ?? 'Scheduled',
        isCompleted: s['is_completed'] == true,
        isCurrent: s['is_current'] == true,
      )).toList();
    }

    return BusInfo(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      busNumber: json['bus_number'] ?? json['bus'] ?? 'School Bus #14',
      plateNumber: json['plate_number'] ?? json['vehicle_number'] ?? 'BA 2 KHA 8492',
      driverName: json['driver_name'] ?? json['driver']?['name'] ?? 'Hari Shrestha',
      driverPhone: json['driver_phone'] ?? json['driver']?['phone'] ?? '+977 9841234567',
      helperName: json['helper_name'] ?? 'Bikash Tamang',
      helperPhone: json['helper_phone'] ?? '+977 9812345678',
      route: json['route_name'] ?? json['route'] ?? 'Route 4: Kalanki - Baneshwor - School',
      status: json['status'] ?? 'On Time & Moving',
      eta: json['eta'] ?? '8 mins',
      currentStop: json['current_stop'] ?? 'Minbhawan Bridge',
      nextStop: json['next_stop'] ?? 'School Gate (Drop Point)',
      lat: (json['latitude'] != null) ? (double.tryParse(json['latitude'].toString()) ?? 27.6915) : 27.6915,
      lng: (json['longitude'] != null) ? (double.tryParse(json['longitude'].toString()) ?? 85.3420) : 85.3420,
      speedKmh: (json['speed'] != null) ? (double.tryParse(json['speed'].toString()) ?? 34.0) : 34.0,
      stops: stopItems,
    );
  }
}

// ---------------------------------------------------------------------------
// 8. MESSAGES & DIRECT CHAT
// ---------------------------------------------------------------------------
class ConversationItem {
  final int id;
  final int teacherId;
  final String teacherName, teacherRole, teacherAvatar, subject, lastMessage, lastMessageTime;
  final int unreadCount;
  final bool isOnline;

  const ConversationItem({
    required this.id,
    required this.teacherId,
    required this.teacherName,
    this.teacherRole = 'Subject Teacher',
    required this.teacherAvatar,
    required this.subject,
    required this.lastMessage,
    required this.lastMessageTime,
    this.unreadCount = 0,
    this.isOnline = true,
  });

  factory ConversationItem.fromJson(Map<String, dynamic> json) {
    return ConversationItem(
      id: json['id'] is int ? json['id'] : (int.tryParse(json['id']?.toString() ?? '') ?? 1),
      teacherId: json['teacher_id'] ?? json['teacher']?['id'] ?? 1,
      teacherName: json['teacher_name'] ?? json['teacher']?['name'] ?? 'Teacher',
      teacherRole: json['teacher_role'] ?? json['teacher']?['designation'] ?? 'Faculty Member',
      teacherAvatar: json['teacher_avatar'] ?? json['teacher']?['avatar'] ?? 'T',
      subject: json['subject'] ?? 'Mathematics',
      lastMessage: json['last_message'] ?? json['message'] ?? 'Please check the latest assignment instructions.',
      lastMessageTime: json['last_message_time'] ?? json['created_at'] ?? '10:45 AM',
      unreadCount: json['unread_count'] ?? json['unread'] ?? 0,
      isOnline: json['is_online'] ?? true,
    );
  }
}

class ChatMessage {
  final int? id;
  final String sender, message, time;
  final bool isMe;
  final String? attachmentUrl;

  const ChatMessage({
    this.id,
    required this.sender,
    required this.message,
    required this.time,
    required this.isMe,
    this.attachmentUrl,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'] is int ? json['id'] : null,
      sender: json['sender']?['name'] ?? json['sender_name'] ?? 'User',
      message: json['message'] ?? json['content'] ?? '',
      time: json['created_at'] ?? json['time'] ?? 'Just now',
      isMe: json['is_me'] == true || json['sender_type'] == 'parent',
      attachmentUrl: json['attachment'] ?? json['file_url'],
    );
  }
}

// ---------------------------------------------------------------------------
// 9. TEACHERS & CONTACTS
// ---------------------------------------------------------------------------
class TeacherContact {
  final int id;
  final String name, subject, designation, email, phone, avatar, roomNo, consultationHours;
  final double rating;
  final bool isAvailable;

  const TeacherContact({
    required this.id,
    required this.name,
    required this.subject,
    this.designation = 'Senior Teacher',
    required this.email,
    required this.phone,
    required this.avatar,
    this.roomNo = 'Staff Room #2',
    this.consultationHours = '02:00 PM - 03:30 PM (Mon-Fri)',
    this.rating = 4.9,
    this.isAvailable = true,
  });

  factory TeacherContact.fromJson(Map<String, dynamic> json) {
    return TeacherContact(
      id: json['id'] is int ? json['id'] : (int.tryParse(json['id']?.toString() ?? '') ?? 1),
      name: json['name'] ?? json['full_name'] ?? 'Faculty Member',
      subject: json['subject']?['name'] ?? json['subject_name'] ?? json['subject'] ?? 'Science & Tech',
      designation: json['designation'] ?? json['role'] ?? 'Senior Subject Teacher',
      email: json['email'] ?? 'teacher@nepalschoolhub.com',
      phone: json['phone'] ?? '+977 9851000000',
      avatar: json['avatar'] ?? json['photo'] ?? 'T',
      roomNo: json['room_no'] ?? json['room'] ?? 'Staff Room #3',
      consultationHours: json['consultation_hours'] ?? '02:00 PM - 03:30 PM',
      rating: (json['rating'] != null) ? (double.tryParse(json['rating'].toString()) ?? 4.9) : 4.9,
      isAvailable: json['is_available'] ?? true,
    );
  }
}

// ---------------------------------------------------------------------------
// 10. MEETINGS & CONFERENCES (PTA)
// ---------------------------------------------------------------------------
class MeetingItem {
  final int? id;
  final String title, teacherName, teacherAvatar, subject, date, time, duration, status, meetingType;
  final String? meetLink, notes, childName;

  const MeetingItem({
    this.id,
    required this.title,
    required this.teacherName,
    required this.teacherAvatar,
    required this.subject,
    required this.date,
    required this.time,
    this.duration = '25 mins',
    required this.status,
    this.meetingType = 'Video Call (Google Meet)',
    this.meetLink,
    this.notes,
    this.childName = 'Aarav Sharma',
  });

  factory MeetingItem.fromJson(Map<String, dynamic> json) {
    return MeetingItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      title: json['title'] ?? json['purpose'] ?? 'Parent-Teacher Progress Discussion',
      teacherName: json['teacher']?['name'] ?? json['teacher_name'] ?? 'Mr. K. Sharma',
      teacherAvatar: json['teacher']?['avatar'] ?? 'K',
      subject: json['subject']?['name'] ?? json['subject'] ?? 'Mathematics & Science',
      date: json['date'] ?? json['meeting_date'] ?? '2026-09-28',
      time: json['time'] ?? json['start_time'] ?? '03:15 PM',
      duration: json['duration'] ?? '25 mins',
      status: json['status'] ?? 'confirmed',
      meetingType: json['type'] ?? json['meeting_type'] ?? 'Online Video Conference',
      meetLink: json['meet_link'] ?? json['link'] ?? 'https://meet.google.com/nsh-parent-call',
      notes: json['notes'] ?? 'Reviewing midterm scores and Olympiad readiness.',
      childName: json['child_name'] ?? json['student']?['name'] ?? 'Student',
    );
  }
}

// ---------------------------------------------------------------------------
// 11. EVENTS & SCHOOL CALENDAR
// ---------------------------------------------------------------------------
class EventItem {
  final int? id;
  final String title, description, category, startDate, endDate, time, location;
  final bool isHoliday, isReminderSet;
  final String rsvpStatus; // 'going', 'interested', 'none'
  final int colorHex;

  const EventItem({
    this.id,
    required this.title,
    required this.description,
    required this.category,
    required this.startDate,
    this.endDate = '',
    required this.time,
    required this.location,
    this.isHoliday = false,
    this.isReminderSet = false,
    this.rsvpStatus = 'going',
    required this.colorHex,
  });

  factory EventItem.fromJson(Map<String, dynamic> json) {
    return EventItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      title: json['title'] ?? json['name'] ?? 'School Event',
      description: json['description'] ?? 'Annual inter-school cultural gathering and exhibition.',
      category: json['category'] ?? json['type'] ?? 'Academic',
      startDate: json['start_date'] ?? json['date'] ?? '2026-10-02',
      endDate: json['end_date'] ?? '',
      time: json['time'] ?? '10:00 AM - 04:00 PM',
      location: json['location'] ?? json['venue'] ?? 'Main School Auditorium',
      isHoliday: json['is_holiday'] == true || json['type'] == 'holiday',
      isReminderSet: json['reminder_set'] == true,
      rsvpStatus: json['rsvp_status'] ?? 'going',
      colorHex: json['color_hex'] ?? 0xFF00B894,
    );
  }
}

// ---------------------------------------------------------------------------
// 12. HEALTH & MEDICAL RECORDS
// ---------------------------------------------------------------------------
class VaccinationItem {
  final String vaccineName, givenDate, dueDate, doctorName, status;

  const VaccinationItem({
    required this.vaccineName,
    required this.givenDate,
    required this.dueDate,
    this.doctorName = 'School Medical Officer',
    required this.status,
  });

  factory VaccinationItem.fromJson(Map<String, dynamic> json) {
    return VaccinationItem(
      vaccineName: json['vaccine_name'] ?? json['name'] ?? 'Tetanus Toxoid Booster',
      givenDate: json['given_date'] ?? json['administered_at'] ?? '2026-02-10',
      dueDate: json['due_date'] ?? 'Up to date',
      doctorName: json['doctor_name'] ?? 'Dr. Anupa Thapa (MD)',
      status: json['status'] ?? 'Completed',
    );
  }
}

class HealthProfile {
  final String bloodGroup, height, weight, bmi, emergencyContact, doctorContact;
  final List<String> allergies, medications, chronicConditions;
  final List<VaccinationItem> vaccinations;

  const HealthProfile({
    this.bloodGroup = 'B+ Positive',
    this.height = '148 cm',
    this.weight = '42 kg',
    this.bmi = '19.2 (Normal)',
    this.emergencyContact = '+977 9841000001 (Father)',
    this.doctorContact = '+977 1-4433221 (Family Clinic)',
    this.allergies = const ['Peanuts (Mild)', 'Dust mite'],
    this.medications = const ['Inhaler as needed'],
    this.chronicConditions = const ['Mild Exercise-induced Asthma'],
    this.vaccinations = const [],
  });
}

// ---------------------------------------------------------------------------
// 13. BEHAVIOR, MERITS & DISCIPLINE
// ---------------------------------------------------------------------------
class BehaviorEntry {
  final int? id;
  final String title, description, category, type, date, teacherName;
  final int points;
  final int colorHex;

  const BehaviorEntry({
    this.id,
    required this.title,
    required this.description,
    required this.category,
    required this.type, // 'merit' or 'warning'
    required this.date,
    required this.teacherName,
    required this.points,
    required this.colorHex,
  });

  factory BehaviorEntry.fromJson(Map<String, dynamic> json) {
    final isMerit = (json['type'] == 'merit' || (json['points'] ?? 0) >= 0);
    return BehaviorEntry(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      title: json['title'] ?? (isMerit ? 'Excellence in Science Project' : 'Classroom Disruption Warning'),
      description: json['description'] ?? 'Displayed high leadership and team spirit in lab practicals.',
      category: json['category'] ?? (isMerit ? 'Leadership & Academics' : 'Discipline'),
      type: isMerit ? 'merit' : 'warning',
      date: json['date'] ?? json['created_at'] ?? '2026-09-20',
      teacherName: json['teacher_name'] ?? json['teacher']?['name'] ?? 'Mr. K. Sharma',
      points: json['points'] ?? (isMerit ? 15 : -5),
      colorHex: json['color_hex'] ?? (isMerit ? 0xFF05C46B : 0xFFFF3838),
    );
  }
}

// ---------------------------------------------------------------------------
// 14. LEAVE REQUESTS
// ---------------------------------------------------------------------------
class LeaveRequestItem {
  final int? id;
  final String childName, leaveType, startDate, endDate, reason, status, appliedDate, approvedBy, remarks;
  final int totalDays;

  const LeaveRequestItem({
    this.id,
    required this.childName,
    required this.leaveType,
    required this.startDate,
    required this.endDate,
    required this.reason,
    required this.status,
    required this.appliedDate,
    this.approvedBy = 'Class Teacher',
    this.remarks = 'Approved. Catch up on homework afterwards.',
    required this.totalDays,
  });

  factory LeaveRequestItem.fromJson(Map<String, dynamic> json) {
    return LeaveRequestItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      childName: json['child_name'] ?? json['student']?['name'] ?? 'Student',
      leaveType: json['leave_type'] ?? json['type'] ?? 'Sick / Medical Leave',
      startDate: json['start_date'] ?? '2026-09-22',
      endDate: json['end_date'] ?? '2026-09-23',
      reason: json['reason'] ?? 'Viral fever recovery and doctor prescription rest.',
      status: json['status'] ?? 'approved',
      appliedDate: json['applied_at'] ?? json['created_at'] ?? '2026-09-21',
      approvedBy: json['approved_by'] ?? 'Principal / Class Teacher',
      remarks: json['remarks'] ?? 'Leave sanctioned.',
      totalDays: json['total_days'] ?? json['days'] ?? 2,
    );
  }
}

// ---------------------------------------------------------------------------
// 15. NOTIFICATIONS
// ---------------------------------------------------------------------------
class AppNotification {
  final int? id;
  final String title, body, time, type, childName;
  final bool isRead;

  const AppNotification({
    this.id,
    required this.title,
    required this.body,
    required this.time,
    required this.type,
    required this.isRead,
    required this.childName,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      title: json['title'] ?? 'Notification',
      body: json['body'] ?? json['message'] ?? '',
      time: json['time'] ?? json['created_at'] ?? 'Just now',
      type: json['type'] ?? 'general',
      isRead: json['is_read'] == true || json['read_at'] != null,
      childName: json['child_name'] ?? 'Student',
    );
  }
}

// ---------------------------------------------------------------------------
// 16. DIGITAL DOCUMENTS & VAULT
// ---------------------------------------------------------------------------
class DocumentItem {
  final int? id;
  final String title, type, fileSize, date, downloadUrl;

  const DocumentItem({
    this.id,
    required this.title,
    required this.type,
    required this.fileSize,
    required this.date,
    required this.downloadUrl,
  });

  factory DocumentItem.fromJson(Map<String, dynamic> json) {
    return DocumentItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      title: json['title'] ?? json['name'] ?? 'Document File.pdf',
      type: json['type'] ?? json['mime_type'] ?? 'PDF',
      fileSize: json['size'] ?? json['file_size'] ?? '1.2 MB',
      date: json['date'] ?? json['created_at'] ?? '2026-08-20',
      downloadUrl: json['url'] ?? json['download_url'] ?? '',
    );
  }
}

// ---------------------------------------------------------------------------
// ICON HELPERS & STATIC MOCK DATA REPOSITORY
// ---------------------------------------------------------------------------
const _icons = <int, IconData>{
  0xe559: Icons.calculate_outlined,
  0xe40a: Icons.science_outlined,
  0xe3e9: Icons.menu_book_outlined,
  0xe00c: Icons.public_outlined,
  0xe3f4: Icons.computer_outlined,
  0xe335: Icons.palette_outlined,
  0xe566: Icons.fitness_center_outlined,
};

IconData subjectIcon(int code) => _icons[code] ?? Icons.book_outlined;

class AppData {
  static const List<Child> children = [
    Child(
      id: 1,
      name: 'Aarav Sharma',
      firstName: 'Aarav',
      lastName: 'Sharma',
      grade: 'Grade 8',
      section: 'Section A',
      avatar: 'A',
      schoolName: 'Nepal Model Secondary School',
      rollNo: '14',
      admissionNo: 'ADM-2026-089',
      bloodGroup: 'B+ Positive',
      birthday: '2012-05-14',
      gender: 'Male',
      gpa: 3.9,
      attendance: 0.94,
      colorHex: 0xFF6C3CE9,
      rank: 2,
    ),
    Child(
      id: 2,
      name: 'Ananya Sharma',
      firstName: 'Ananya',
      lastName: 'Sharma',
      grade: 'Grade 5',
      section: 'Section B',
      avatar: 'N',
      schoolName: 'Nepal Model Secondary School',
      rollNo: '08',
      admissionNo: 'ADM-2026-142',
      bloodGroup: 'O+ Positive',
      birthday: '2015-11-20',
      gender: 'Female',
      gpa: 3.8,
      attendance: 0.96,
      colorHex: 0xFF00D2D3,
      rank: 4,
    ),
  ];

  static const List<SubjectGrade> subjects = [
    SubjectGrade(
      name: 'Mathematics',
      code: 'MTH801',
      grade: 'A+',
      teacher: 'Prof. Ram K. Shrestha',
      score: 96.0,
      progress: 0.96,
      colorHex: 0xFF6C3CE9,
      iconCode: 0xe559,
    ),
    SubjectGrade(
      name: 'Science & Environment',
      code: 'SCI802',
      grade: 'A',
      teacher: 'Dr. Gita Adhikari',
      score: 91.0,
      progress: 0.91,
      colorHex: 0xFF00D2D3,
      iconCode: 0xe40a,
    ),
    SubjectGrade(
      name: 'English Literature',
      code: 'ENG803',
      grade: 'A',
      teacher: 'Ms. Sunita Rai',
      score: 89.0,
      progress: 0.89,
      colorHex: 0xFFFF9F1A,
      iconCode: 0xe3e9,
    ),
    SubjectGrade(
      name: 'Nepali Language',
      code: 'NEP804',
      grade: 'A+',
      teacher: 'Mr. Bimal Poudel',
      score: 94.0,
      progress: 0.94,
      colorHex: 0xFF05C46B,
      iconCode: 0xe00c,
    ),
    SubjectGrade(
      name: 'Computer Science & AI',
      code: 'CSC805',
      grade: 'A+',
      teacher: 'Er. Sandeep Thapa',
      score: 98.0,
      progress: 0.98,
      colorHex: 0xFF2E86DE,
      iconCode: 0xe3f4,
    ),
  ];

  static List<Assignment> assignments = [
    const Assignment(
      id: 1,
      title: 'Quadratic Equations Worksheet #4',
      subject: 'Mathematics',
      description: 'Solve questions 1 through 15 with graph plot for vertex.',
      dueDate: 'Tomorrow, 5:00 PM',
      isCompleted: false,
      priority: 'high',
      colorHex: 0xFF6C3CE9,
      childName: 'Aarav Sharma',
    ),
    const Assignment(
      id: 2,
      title: 'Photosynthesis Lab Observations',
      subject: 'Science',
      description: 'Submit chloroplast microscopic observation summary diagram.',
      dueDate: 'In 3 days',
      isCompleted: false,
      priority: 'medium',
      colorHex: 0xFF00D2D3,
      childName: 'Aarav Sharma',
    ),
    const Assignment(
      id: 3,
      title: 'Nepali Nibandha: Hamro Desh Nepal',
      subject: 'Nepali',
      description: 'Write a 300-word essay on cultural heritage of Nepal.',
      dueDate: 'Completed',
      isCompleted: true,
      priority: 'low',
      colorHex: 0xFF05C46B,
      childName: 'Aarav Sharma',
    ),
  ];

  static const List<ExamItem> exams = [
    ExamItem(
      id: 1,
      subject: 'Mathematics Final Term',
      date: '2026-10-05',
      time: '09:00 AM - 12:00 PM',
      room: 'Hall A - Seat 24',
      type: 'Second Term Examination',
      daysLeft: 9,
      colorHex: 0xFFFF9F1A,
      childName: 'Aarav Sharma',
    ),
    ExamItem(
      id: 2,
      subject: 'Science & Technology',
      date: '2026-10-07',
      time: '09:00 AM - 12:00 PM',
      room: 'Hall A - Seat 24',
      type: 'Second Term Examination',
      daysLeft: 11,
      colorHex: 0xFF00D2D3,
      childName: 'Aarav Sharma',
    ),
  ];

  static const List<AttendanceRecord> attendance = [
    AttendanceRecord(
      subject: 'Mathematics',
      childName: 'Aarav Sharma',
      present: 24,
      total: 25,
      colorHex: 0xFF05C46B,
    ),
    AttendanceRecord(
      subject: 'Science',
      childName: 'Aarav Sharma',
      present: 23,
      total: 25,
      colorHex: 0xFF05C46B,
    ),
    AttendanceRecord(
      subject: 'Computer Science',
      childName: 'Aarav Sharma',
      present: 25,
      total: 25,
      colorHex: 0xFF05C46B,
    ),
    AttendanceRecord(
      subject: 'English',
      childName: 'Aarav Sharma',
      present: 22,
      total: 25,
      colorHex: 0xFFFF9F1A,
    ),
  ];

  static const BusInfo busInfo = BusInfo(
    busNumber: 'Bus #14 (Route 4)',
    plateNumber: 'BA 2 KHA 8492',
    driverName: 'Hari Shrestha',
    driverPhone: '+977 9841234567',
    helperName: 'Bikash Tamang',
    helperPhone: '+977 9812345678',
    route: 'Kalanki - Baneshwor - School',
    status: 'On Time & Moving',
    eta: '6 mins',
    lat: 27.6915,
    lng: 85.3420,
    speedKmh: 38.0,
    stops: [
      BusStopItem(name: 'Kalanki Hub', arrivalTime: '07:45 AM', status: 'Passed', isCompleted: true),
      BusStopItem(name: 'Tripureshwor Chowk', arrivalTime: '08:05 AM', status: 'Passed', isCompleted: true),
      BusStopItem(name: 'Maitighar Mandala', arrivalTime: '08:20 AM', status: 'Passed', isCompleted: true),
      BusStopItem(name: 'Baneshwor Terminal (Your Stop)', arrivalTime: '08:32 AM', status: 'Approaching', isCurrent: true),
      BusStopItem(name: 'School Campus Gate', arrivalTime: '08:45 AM', status: 'Final Destination'),
    ],
  );

  static const List<FeeRecord> fees = [
    FeeRecord(
      id: 1,
      title: 'September 2026 Tuition & Lab Fee',
      invoiceNumber: 'INV-2026-0914',
      dueDate: '2026-09-30',
      status: 'pending',
      amount: 14500.0,
      paidAmount: 0.0,
      colorHex: 0xFFFF9F1A,
    ),
    FeeRecord(
      id: 2,
      title: 'August 2026 Monthly Tuition Fee',
      invoiceNumber: 'INV-2026-0810',
      dueDate: '2026-08-31',
      paidDate: '2026-08-25',
      status: 'paid',
      amount: 14500.0,
      paidAmount: 14500.0,
      colorHex: 0xFF05C46B,
    ),
    FeeRecord(
      id: 3,
      title: 'Annual Sports & Activity Kit Fee',
      invoiceNumber: 'INV-2026-0402',
      dueDate: '2026-05-15',
      paidDate: '2026-05-10',
      status: 'paid',
      amount: 6000.0,
      paidAmount: 6000.0,
      colorHex: 0xFF05C46B,
    ),
  ];

  static const List<AppNotification> notifications = [
    AppNotification(
      id: 1,
      title: 'Bus Approaching Your Stop',
      body: 'Bus #14 is 5 minutes away from Baneshwor Terminal.',
      time: '2m ago',
      type: 'bus',
      isRead: false,
      childName: 'Aarav Sharma',
    ),
    AppNotification(
      id: 2,
      title: 'Math Quiz Score Published',
      body: 'Aarav scored 96/100 (Grade A+) in Unit 4 Quiz.',
      time: '1h ago',
      type: 'grade',
      isRead: false,
      childName: 'Aarav Sharma',
    ),
    AppNotification(
      id: 3,
      title: 'September Fee Invoice Ready',
      body: 'Tuition invoice INV-2026-0914 due by end of month.',
      time: 'Yesterday',
      type: 'fee',
      isRead: true,
      childName: 'Aarav Sharma',
    ),
  ];

  static const List<Map<String, dynamic>> schedule = [
    {
      'time': '09:00 AM',
      'subject': 'Mathematics (Algebra II)',
      'teacher': 'Prof. Ram K. Shrestha',
      'room': 'Room 204',
      'duration': '45 mins',
      'colorHex': 0xFFEDE7F6,
    },
    {
      'time': '09:50 AM',
      'subject': 'Science & Lab Practicals',
      'teacher': 'Dr. Gita Adhikari',
      'room': 'Physics Lab 1',
      'duration': '45 mins',
      'colorHex': 0xFFE0F7FA,
    },
    {
      'time': '10:40 AM',
      'subject': 'English Literature',
      'teacher': 'Ms. Sunita Rai',
      'room': 'Room 204',
      'duration': '40 mins',
      'colorHex': 0xFFFFF3E0,
    },
    {
      'time': '11:45 AM',
      'subject': 'Computer Science & Python',
      'teacher': 'Er. Sandeep Thapa',
      'room': 'IT Lab 2',
      'duration': '50 mins',
      'colorHex': 0xFFE8F5E9,
    },
  ];
}
