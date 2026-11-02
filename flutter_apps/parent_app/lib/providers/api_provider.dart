import 'package:flutter/material.dart';
import '../data/app_data.dart';
import '../services/api_service.dart';

class ApiProvider extends ChangeNotifier {
  bool _isLoading = false;
  Map<String, dynamic>? _userProfile;
  List<Child> _children = [];
  Child? _selectedChild;
  
  List<Assignment> _assignments = [];
  List<AttendanceRecord> _attendance = [];
  List<ExamItem> _exams = [];
  List<FeeRecord> _fees = [];
  List<SubjectGrade> _subjects = [];
  List<ReportCard> _reportCards = [];
  List<AppNotification> _notifications = [];
  List<TeacherContact> _teachers = [];
  List<MeetingItem> _meetings = [];
  List<EventItem> _events = [];
  List<LeaveRequestItem> _leaveRequests = [];
  List<BehaviorEntry> _behaviorRecords = [];
  List<DocumentItem> _documents = [];
  List<ConversationItem> _conversations = [];
  HealthProfile _healthProfile = const HealthProfile();
  BusInfo _busInfo = AppData.busInfo;

  bool get isLoading => _isLoading;
  Map<String, dynamic>? get userProfile => _userProfile;
  List<Child> get children => _children.isNotEmpty ? _children : AppData.children;
  Child get selectedChild => _selectedChild ?? (children.isNotEmpty ? children.first : AppData.children.first);
  
  List<Assignment> get assignments => _assignments.isNotEmpty ? _assignments : AppData.assignments;
  List<AttendanceRecord> get attendance => _attendance.isNotEmpty ? _attendance : AppData.attendance;
  List<ExamItem> get exams => _exams.isNotEmpty ? _exams : AppData.exams;
  List<FeeRecord> get fees => _fees.isNotEmpty ? _fees : AppData.fees;
  List<SubjectGrade> get subjects => _subjects.isNotEmpty ? _subjects : AppData.subjects;
  List<ReportCard> get reportCards => _reportCards.isNotEmpty ? _reportCards : const [
    ReportCard(
      id: 1,
      term: 'Second Terminal Examination 2081',
      academicYear: '2081-2082 (2026)',
      issueDate: '2081-08-15 (Dec 2026)',
      overallGrade: 'A+',
      principalRemarks: 'Outstanding intellectual curiosity, exemplary discipline, and remarkable leadership in academic & extra-curricular activities.',
      gpa: 3.90,
      percentage: 91.5,
      totalMarks: 700,
      obtainedMarks: 641,
      rank: 2,
      totalStudents: 42,
      attendance: '96 / 102 Days (94.1%)',
      verifyToken: 'NSH-VERIFIED-2081-8849',
      verifyUrl: 'https://verify.nepalschoolhub.com/transcripts/1',
      classTeacher: 'Mrs. S. Adhikari',
      examController: 'K. R. Pandey',
      principalName: 'Dr. B. K. Sharma',
      division: 'First Division with Distinction',
      resultStatus: 'PASSED',
      subjectGrades: AppData.subjects,
    ),
    ReportCard(
      id: 2,
      term: 'First Terminal Examination 2081',
      academicYear: '2081-2082 (2026)',
      issueDate: '2081-04-20 (Aug 2026)',
      overallGrade: 'A',
      principalRemarks: 'Very good intellectual start to the session. Keep striving for distinction in all subjects.',
      gpa: 3.75,
      percentage: 88.0,
      totalMarks: 700,
      obtainedMarks: 616,
      rank: 4,
      totalStudents: 42,
      attendance: '48 / 50 Days (96.0%)',
      verifyToken: 'NSH-VERIFIED-2081-8848',
      verifyUrl: 'https://verify.nepalschoolhub.com/transcripts/2',
      classTeacher: 'Mrs. S. Adhikari',
      examController: 'K. R. Pandey',
      principalName: 'Dr. B. K. Sharma',
      division: 'First Division',
      resultStatus: 'PASSED',
      subjectGrades: AppData.subjects,
    ),
    ReportCard(
      id: 3,
      term: 'Annual / Final Terminal Examination 2080',
      academicYear: '2080-2081 (2025)',
      issueDate: '2081-01-05 (April 2026)',
      overallGrade: 'A+',
      principalRemarks: 'Promoted to Grade 8 with Outstanding Distinction. Exemplary academic standard and moral character.',
      gpa: 3.92,
      percentage: 92.4,
      totalMarks: 700,
      obtainedMarks: 647,
      rank: 1,
      totalStudents: 40,
      attendance: '198 / 205 Days (96.5%)',
      verifyToken: 'NSH-VERIFIED-2080-7712',
      verifyUrl: 'https://verify.nepalschoolhub.com/transcripts/3',
      classTeacher: 'Mrs. S. Adhikari',
      examController: 'K. R. Pandey',
      principalName: 'Dr. B. K. Sharma',
      division: 'First Division with Distinction',
      resultStatus: 'PROMOTED',
      subjectGrades: AppData.subjects,
    ),
  ];
  List<AppNotification> get notifications => _notifications.isNotEmpty ? _notifications : AppData.notifications;
  List<TeacherContact> get teachers => _teachers;
  List<MeetingItem> get meetings => _meetings;
  List<EventItem> get events => _events;
  List<LeaveRequestItem> get leaveRequests => _leaveRequests;
  List<BehaviorEntry> get behaviorRecords => _behaviorRecords;
  List<DocumentItem> get documents => _documents;
  List<ConversationItem> get conversations => _conversations;
  HealthProfile get healthProfile => _healthProfile;
  BusInfo get busInfo => _busInfo;

  int get unreadNotificationsCount => notifications.where((n) => !n.isRead).length;

  ApiProvider() {
    loadAllData();
  }

  void selectChild(Child child) {
    _selectedChild = child;
    notifyListeners();
    loadChildSpecificData();
  }

  Future<void> loadAllData() async {
    _isLoading = true;
    notifyListeners();

    try {
      final results = await Future.wait([
        ApiService.getProfile(),
        ApiService.getChildren(),
        ApiService.getNotifications(),
        ApiService.getEvents(),
        ApiService.getTeachers(),
        ApiService.getConversations(),
      ]);

      _userProfile = results[0] as Map<String, dynamic>?;
      _children = results[1] as List<Child>;
      if (_children.isNotEmpty) {
        _selectedChild = _children.first;
      }
      _notifications = results[2] as List<AppNotification>;
      _events = results[3] as List<EventItem>;
      _teachers = results[4] as List<TeacherContact>;
      _conversations = results[5] as List<ConversationItem>;

      await loadChildSpecificData();
    } catch (_) {
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadChildSpecificData() async {
    final childId = _selectedChild?.id;
    try {
      final results = await Future.wait([
        ApiService.getAssignments(childId: childId),
        ApiService.getAttendance(childId: childId),
        ApiService.getExams(childId: childId),
        ApiService.getFees(childId: childId),
        ApiService.getSubjects(childId: childId),
        ApiService.getReportCards(childId: childId),
        ApiService.getBusInfo(childId: childId),
        ApiService.getMeetings(childId: childId),
        ApiService.getLeaveRequests(childId: childId),
        ApiService.getBehaviorRecords(childId: childId),
        ApiService.getChildHealth(childId),
        ApiService.getDocuments(childId: childId),
      ]);

      _assignments = results[0] as List<Assignment>;
      _attendance = results[1] as List<AttendanceRecord>;
      _exams = results[2] as List<ExamItem>;
      _fees = results[3] as List<FeeRecord>;
      _subjects = results[4] as List<SubjectGrade>;
      _reportCards = results[5] as List<ReportCard>;
      _busInfo = (results[6] as BusInfo?) ?? AppData.busInfo;
      _meetings = results[7] as List<MeetingItem>;
      _leaveRequests = results[8] as List<LeaveRequestItem>;
      _behaviorRecords = results[9] as List<BehaviorEntry>;
      _healthProfile = results[10] as HealthProfile;
      _documents = results[11] as List<DocumentItem>;
    } catch (_) {}
    notifyListeners();
  }

  Future<void> refreshReportCards() async {
    try {
      final childId = selectedChild.id;
      final cards = await ApiService.getReportCards(childId: childId);
      if (cards.isNotEmpty) {
        _reportCards = cards;
        notifyListeners();
      }
    } catch (_) {}
  }
}
