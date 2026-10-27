class TeacherClass {
  final String name, section, room, time, subject, period;
  final int students, colorHex, iconCode;
  final double avgGrade;
  final bool isOngoing;
  final int? id;
  final int? classId;
  final int? sectionId;
  final int? courseId;
  final bool isClassTeacher;

  const TeacherClass({
    required this.name,
    required this.section,
    required this.room,
    required this.time,
    required this.subject,
    required this.students,
    required this.colorHex,
    required this.iconCode,
    required this.avgGrade,
    this.period = 'Period 1',
    this.isOngoing = false,
    this.id,
    this.classId,
    this.sectionId,
    this.courseId,
    this.isClassTeacher = false,
  });

  factory TeacherClass.fromJson(Map<String, dynamic> json, [int index = 0]) {
    final palette = [0xFFE0F2FE, 0xFFEDE9FE, 0xFFDCFCE7, 0xFFFEF3C7, 0xFFFFEDD5, 0xFFFCE7F3];
    final color = json['colorHex'] is int
        ? json['colorHex'] as int
        : palette[index % palette.length];

    final sec = json['section']?.toString() ??
        (json['class_name'] != null ? '${json['class_name']} — ${json['section_name'] ?? 'A'}' : 'Grade — Section');

    return TeacherClass(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      classId: json['class_id'] is int ? json['class_id'] : int.tryParse(json['class_id']?.toString() ?? ''),
      sectionId: json['section_id'] is int ? json['section_id'] : int.tryParse(json['section_id']?.toString() ?? ''),
      courseId: json['course_id'] is int ? json['course_id'] : int.tryParse(json['course_id']?.toString() ?? ''),
      name: json['name']?.toString() ?? json['subject']?.toString() ?? 'General Course',
      section: sec,
      room: json['room']?.toString() ?? 'Room ${101 + index}',
      time: json['time']?.toString() ?? '09:${((index * 45) % 60).toString().padLeft(2, '0')} AM',
      subject: json['subject']?.toString() ?? json['name']?.toString() ?? 'Subject',
      period: json['period']?.toString() ?? 'Period ${index + 1}',
      students: json['students'] is int
          ? json['students'] as int
          : (int.tryParse(json['students']?.toString() ?? '') ?? 0),
      colorHex: color,
      iconCode: json['iconCode'] is int ? json['iconCode'] as int : 0xe3e9,
      avgGrade: (json['avgGrade'] is num) ? (json['avgGrade'] as num).toDouble() : 3.6,
      isOngoing: json['isOngoing'] == true,
      isClassTeacher: json['is_class_teacher'] == true,
    );
  }
}

class Student {
  final String name, grade, avatar, rollNo;
  final double gpa, attendance;
  final int rank;
  final int? id;
  final String? email;
  final String? phone;
  final int? sectionId;
  final int? classId;
  final String? photoUrl;

  const Student({
    required this.name,
    required this.grade,
    required this.avatar,
    required this.gpa,
    required this.attendance,
    required this.rank,
    this.rollNo = '01',
    this.id,
    this.email,
    this.phone,
    this.sectionId,
    this.classId,
    this.photoUrl,
  });

  factory Student.fromJson(Map<String, dynamic> json, [int index = 0]) {
    final name = json['name']?.toString() ??
        (json['first_name'] != null ? '${json['first_name']} ${json['last_name'] ?? ''}'.trim() : 'Student ${index + 1}');
    final initials = name.split(' ').where((e) => e.isNotEmpty).map((e) => e[0]).take(2).join();

    final gpaVal = json['gpa'] is num
        ? (json['gpa'] as num).toDouble()
        : (double.tryParse(json['gpa']?.toString() ?? '') ?? 3.5);

    final attVal = json['attendance'] is num
        ? (json['attendance'] as num).toDouble()
        : (double.tryParse(json['attendance']?.toString() ?? '') ?? 0.95);

    final gradeStr = json['grade']?.toString() ??
        (gpaVal >= 3.6 ? 'A+' : (gpaVal >= 3.2 ? 'A' : (gpaVal >= 2.8 ? 'B+' : 'B')));

    return Student(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      name: name,
      grade: gradeStr,
      avatar: initials.isNotEmpty ? initials : 'ST',
      gpa: gpaVal,
      attendance: attVal > 1.0 ? attVal / 100.0 : attVal,
      rank: json['rank'] is int ? json['rank'] as int : (index + 1),
      rollNo: json['roll_no']?.toString() ?? sprintfRoll(index + 1),
      email: json['email']?.toString(),
      phone: json['phone']?.toString(),
      sectionId: json['section_id'] is int ? json['section_id'] : int.tryParse(json['section_id']?.toString() ?? ''),
      classId: json['class_id'] is int ? json['class_id'] : int.tryParse(json['class_id']?.toString() ?? ''),
      photoUrl: json['photo']?.toString() ?? json['photo_url']?.toString() ?? json['avatar_url']?.toString(),
    );
  }

  static String sprintfRoll(int n) => n < 10 ? '0$n' : '$n';
}

class TeacherAssignment {
  final String title, subject, dueDate, classSection;
  final int submitted, total, colorHex;
  final int? id;

  const TeacherAssignment({
    required this.title,
    required this.subject,
    required this.dueDate,
    required this.classSection,
    required this.submitted,
    required this.total,
    required this.colorHex,
    this.id,
  });

  factory TeacherAssignment.fromJson(Map<String, dynamic> json, [int index = 0]) {
    final palette = [0xFF2563EB, 0xFF7C3AED, 0xFF059669, 0xFFD97706, 0xFFDC2626];
    return TeacherAssignment(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      title: json['title']?.toString() ?? 'Assignment',
      subject: json['subject']?.toString() ?? json['course_name']?.toString() ?? 'Course',
      dueDate: json['due_date']?.toString() ?? 'Upcoming',
      classSection: json['class_name']?.toString() ?? json['section']?.toString() ?? 'Class',
      submitted: json['submissions'] is int ? json['submissions'] as int : (int.tryParse(json['submissions']?.toString() ?? '') ?? 0),
      total: json['total'] is int ? json['total'] as int : (int.tryParse(json['total']?.toString() ?? '') ?? 25),
      colorHex: palette[index % palette.length],
    );
  }
}

class TeacherExam {
  final String subject, date, time, room, type, classSection;
  final int daysLeft, colorHex;
  final int? id;

  const TeacherExam({
    required this.subject,
    required this.date,
    required this.time,
    required this.room,
    required this.type,
    required this.classSection,
    required this.daysLeft,
    required this.colorHex,
    this.id,
  });

  factory TeacherExam.fromJson(Map<String, dynamic> json, [int index = 0]) {
    final palette = [0xFF2563EB, 0xFF7C3AED, 0xFF059669, 0xFFD97706, 0xFFDC2626];
    return TeacherExam(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      subject: json['subject']?.toString() ?? json['title']?.toString() ?? 'Exam',
      date: json['date_ad']?.toString() ?? json['date']?.toString() ?? 'Upcoming',
      time: json['time']?.toString() ?? '09:00 AM',
      room: json['room']?.toString() ?? 'Hall A',
      type: json['type']?.toString() ?? 'Terminal',
      classSection: json['class_name']?.toString() ?? json['section']?.toString() ?? 'Class',
      daysLeft: json['days_left'] is int ? json['days_left'] as int : 5,
      colorHex: palette[index % palette.length],
    );
  }
}

class AppNotification {
  final String title, body, time, type;
  final bool isRead;
  const AppNotification({
    required this.title,
    required this.body,
    required this.time,
    required this.type,
    required this.isRead,
  });
}

class SchoolNotice {
  final String title, date, category, author;
  final bool isUrgent;
  const SchoolNotice({
    required this.title,
    required this.date,
    required this.category,
    required this.author,
    this.isUrgent = false,
  });
}

class AppData {
  static const List<TeacherClass> classes = [
    TeacherClass(
      name: 'Compulsory Mathematics',
      section: 'Grade 10 - A (Mt. Everest)',
      room: 'Room 101 (Block B)',
      time: '08:30 AM - 09:30 AM',
      subject: 'Mathematics',
      period: 'Period 1',
      students: 32,
      colorHex: 0xFFE0F2FE,
      iconCode: 0xe3e9,
      avgGrade: 3.6,
      isOngoing: true,
    ),
    TeacherClass(
      name: 'Opt. Mathematics',
      section: 'Grade 10 - B (Annapurna)',
      room: 'Room 104 (Block B)',
      time: '10:00 AM - 11:00 AM',
      subject: 'Mathematics',
      period: 'Period 3',
      students: 30,
      colorHex: 0xFFEDE9FE,
      iconCode: 0xe3e9,
      avgGrade: 3.4,
    ),
    TeacherClass(
      name: 'Higher Secondary Calculus',
      section: 'Grade 11 - Science A',
      room: 'Room 305 (Science Wing)',
      time: '01:00 PM - 02:00 PM',
      subject: 'Calculus',
      period: 'Period 5',
      students: 28,
      colorHex: 0xFFDCFCE7,
      iconCode: 0xe00c,
      avgGrade: 3.8,
    ),
    TeacherClass(
      name: 'Business Statistics',
      section: 'Grade 12 - Mgmt A',
      room: 'Room 201 (Block C)',
      time: '02:30 PM - 03:30 PM',
      subject: 'Statistics',
      period: 'Period 7',
      students: 25,
      colorHex: 0xFFFEF3C7,
      iconCode: 0xe3e9,
      avgGrade: 3.5,
    ),
  ];

  static const List<Student> students = [
    Student(name: 'Aayush Shrestha', grade: 'A+', avatar: 'AS', gpa: 4.0, attendance: 0.98, rank: 1, rollNo: '01'),
    Student(name: 'Sneha Adhikari', grade: 'A+', avatar: 'SA', gpa: 3.95, attendance: 0.95, rank: 2, rollNo: '02'),
    Student(name: 'Bibek Thapa', grade: 'A', avatar: 'BT', gpa: 3.90, attendance: 0.92, rank: 3, rollNo: '03'),
    Student(name: 'Prashant Karki', grade: 'A', avatar: 'PK', gpa: 3.85, attendance: 0.90, rank: 4, rollNo: '04'),
    Student(name: 'Pooja Sharma', grade: 'A', avatar: 'PS', gpa: 3.80, attendance: 0.88, rank: 5, rollNo: '05'),
    Student(name: 'Rohan Gurung', grade: 'A-', avatar: 'RG', gpa: 3.75, attendance: 0.85, rank: 6, rollNo: '06'),
    Student(name: 'Anjali Tamang', grade: 'A-', avatar: 'AT', gpa: 3.72, attendance: 0.87, rank: 7, rollNo: '07'),
    Student(name: 'Sujan Maharjan', grade: 'B+', avatar: 'SM', gpa: 3.68, attendance: 0.80, rank: 8, rollNo: '08'),
    Student(name: 'Bipana Rai', grade: 'B+', avatar: 'BR', gpa: 3.65, attendance: 0.82, rank: 9, rollNo: '09'),
    Student(name: 'Kushal Neupane', grade: 'B+', avatar: 'KN', gpa: 3.62, attendance: 0.78, rank: 10, rollNo: '10'),
    Student(name: 'Suman Dahal', grade: 'B', avatar: 'SD', gpa: 3.40, attendance: 0.75, rank: 11, rollNo: '11'),
    Student(name: 'Saroj Poudel', grade: 'B', avatar: 'SP', gpa: 3.30, attendance: 0.71, rank: 12, rollNo: '12'),
  ];

  static const List<TeacherAssignment> assignments = [
    TeacherAssignment(
      title: 'Coordinate Geometry Unit Test Paper',
      subject: 'Mathematics',
      dueDate: 'Asoj 12, 2081',
      classSection: 'Grade 10 - A',
      submitted: 28,
      total: 32,
      colorHex: 0xFFE0F2FE,
    ),
    TeacherAssignment(
      title: 'Probability & Statistics Dataset',
      subject: 'Statistics',
      dueDate: 'Asoj 15, 2081',
      classSection: 'Grade 12 - A',
      submitted: 15,
      total: 25,
      colorHex: 0xFFDCFCE7,
    ),
    TeacherAssignment(
      title: 'Trigonometry Heights & Distances',
      subject: 'Mathematics',
      dueDate: 'Asoj 18, 2081',
      classSection: 'Grade 10 - B',
      submitted: 30,
      total: 30,
      colorHex: 0xFFEDE9FE,
    ),
    TeacherAssignment(
      title: 'Derivatives & Limits Problem Set',
      subject: 'Calculus',
      dueDate: 'Asoj 20, 2081',
      classSection: 'Grade 11 - A',
      submitted: 10,
      total: 28,
      colorHex: 0xFFFEF3C7,
    ),
  ];

  static const List<TeacherExam> exams = [
    TeacherExam(
      subject: '1st Terminal Examination 2081',
      date: 'Asoj 25, 2081',
      time: '08:00 AM - 11:00 AM',
      room: 'Exam Hall 1 (Auditorium)',
      type: 'Terminal Exam',
      classSection: 'Grade 10 - A & B',
      daysLeft: 15,
      colorHex: 0xFFE0F2FE,
    ),
    TeacherExam(
      subject: 'Calculus Unit Assessment',
      date: 'Asoj 28, 2081',
      time: '11:30 AM',
      room: 'Room 305',
      type: 'Unit Test',
      classSection: 'Grade 11 - Science',
      daysLeft: 18,
      colorHex: 0xFFEDE9FE,
    ),
  ];

  static const List<SchoolNotice> notices = [
    SchoolNotice(
      title: 'First Terminal Examination 2081 Routine Released',
      date: 'Today, 08:30 AM',
      category: 'Exam Routine',
      author: 'Examination Dept',
      isUrgent: true,
    ),
    SchoolNotice(
      title: 'Emergency Staff Meeting today at 03:30 PM in Hall A',
      date: 'Today, 07:15 AM',
      category: 'Administration',
      author: 'Principal Office',
      isUrgent: true,
    ),
    SchoolNotice(
      title: 'Bada Dashain & Tihar Vacation Notice & Guidelines',
      date: 'Yesterday',
      category: 'General Notice',
      author: 'Administration',
    ),
  ];

  static const List<AppNotification> notifications = [
    AppNotification(
      title: 'Grihakarya Submissions',
      isRead: false,
      type: 'assignment',
      time: '25 min ago',
      body: '28/32 students submitted Coordinate Geometry Unit Test Paper.',
    ),
    AppNotification(
      title: 'Parent Consultation Request',
      isRead: false,
      type: 'meeting',
      time: '1 hour ago',
      body: 'Saroj Poudel\'s parent requested a discussion regarding mathematics progress.',
    ),
    AppNotification(
      title: 'Attendance Alert (Haziri)',
      isRead: false,
      type: 'attendance',
      time: '2 hours ago',
      body: 'Attendance for Grade 10-A is pending submission for 2nd period.',
    ),
    AppNotification(
      title: 'Salary Credited',
      isRead: true,
      type: 'salary',
      time: '1 day ago',
      body: 'Your salary for the month of Bhadra 2081 has been deposited.',
    ),
  ];

  static const List<Map<String, dynamic>> todaySchedule = [
    {
      'period': 'Period 1',
      'time': '08:30 - 09:30 AM',
      'class': 'Compulsory Mathematics',
      'section': 'Grade 10 - A (Mt. Everest)',
      'room': 'Room 101',
      'status': 'In Progress',
      'color': 0xFF2563EB,
      'colorHex': 0xFFE0F2FE,
    },
    {
      'period': 'Period 2',
      'time': '09:30 - 10:15 AM',
      'class': 'Teacher Office Hours / Mentorship',
      'section': 'Faculty Lounge',
      'room': 'Staff Room 2',
      'status': 'Upcoming',
      'color': 0xFF0D9488,
      'colorHex': 0xFFCCFBF1,
    },
    {
      'period': 'Period 3',
      'time': '10:30 - 11:30 AM',
      'class': 'Opt. Mathematics',
      'section': 'Grade 10 - B (Annapurna)',
      'room': 'Room 104',
      'status': 'Upcoming',
      'color': 0xFF7C3AED,
      'colorHex': 0xFFEDE9FE,
    },
    {
      'period': 'Break',
      'time': '11:30 - 12:15 PM',
      'class': 'Tiffin & Lunch Break 🥪',
      'section': 'Cafeteria & Ground',
      'room': 'Canteen',
      'status': 'Upcoming',
      'color': 0xFFEA580C,
      'colorHex': 0xFFFFEDD5,
    },
    {
      'period': 'Period 5',
      'time': '01:00 - 02:00 PM',
      'class': 'Higher Secondary Calculus',
      'section': 'Grade 11 - Science A',
      'room': 'Science Lab 3',
      'status': 'Upcoming',
      'color': 0xFF16A34A,
      'colorHex': 0xFFDCFCE7,
    },
  ];

  static const List<Map<String, dynamic>> teachingTips = [
    {'tip': 'Use practical Nepal curriculum real-world examples in geometry and mensuration.', 'icon': 0xe873},
    {'tip': 'Continuous Assessment System (CAS) marks should be logged weekly for accuracy.', 'icon': 0xe7fd},
    {'tip': 'Encourage active student problem solving on the interactive digital smart-board.', 'icon': 0xe40a},
    {'tip': 'Provide constructive feedback within 48 hours to maximize student retention.', 'icon': 0xe422},
  ];
}
