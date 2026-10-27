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
  List<ReportCard> get reportCards => _reportCards;
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
}
