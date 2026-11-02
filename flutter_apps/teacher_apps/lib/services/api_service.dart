import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final int statusCode;

  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    required this.statusCode,
  });
}

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  // Production base URL - connects to Nepal School Hub Teacher App API
  static const String _defaultBaseUrl = 'https://nshlms.com/api/teacher-app';
  static const String _tokenKey = 'auth_token';
  static const String _teacherDataKey = 'teacher_profile_data';
  static const String _serverUrlKey = 'custom_server_url';
  static const String _sessionTimestampKey = 'session_login_timestamp';
  static const int sessionTimeoutHours = 24; // 24-hour session lifetime

  static void Function()? onSessionExpired;

  String _baseUrl = _defaultBaseUrl;
  String? _token;
  int? _sessionTimestamp;

  String get baseUrl => _baseUrl;
  String? get token => _token;
  int? get sessionTimestamp => _sessionTimestamp;
  bool get isAuthenticated => _token != null && _token!.isNotEmpty && !isSessionExpired();

  /// Check whether the 24-hour session has expired
  bool isSessionExpired() {
    if (_token == null) return false;
    if (_sessionTimestamp == null || _sessionTimestamp == 0) return false;
    final ageMs = DateTime.now().millisecondsSinceEpoch - _sessionTimestamp!;
    return ageMs >= (sessionTimeoutHours * 60 * 60 * 1000);
  }

  /// Remaining duration before the 24-hour session expires
  Duration get remainingSessionTime {
    if (_token == null || _sessionTimestamp == null || _sessionTimestamp == 0) {
      return Duration.zero;
    }
    final ageMs = DateTime.now().millisecondsSinceEpoch - _sessionTimestamp!;
    final totalMs = sessionTimeoutHours * 60 * 60 * 1000;
    final diff = totalMs - ageMs;
    return diff > 0 ? Duration(milliseconds: diff) : Duration.zero;
  }

  /// Exact time the current 24-hour session expires
  DateTime? get sessionExpiresAt {
    if (_sessionTimestamp == null || _sessionTimestamp == 0) return null;
    return DateTime.fromMillisecondsSinceEpoch(_sessionTimestamp!)
        .add(const Duration(hours: sessionTimeoutHours));
  }

  /// Initialize API service and load cached token / server preferences
  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
    final customUrl = prefs.getString(_serverUrlKey);
    if (customUrl != null && customUrl.isNotEmpty) {
      _baseUrl = customUrl;
    }
    _sessionTimestamp = prefs.getInt(_sessionTimestampKey);
    if (_token != null && isSessionExpired()) {
      await logout();
      onSessionExpired?.call();
    }
  }

  /// Change server endpoint dynamically (e.g. for custom school hosting)
  Future<void> setCustomBaseUrl(String url) async {
    _baseUrl = url.endsWith('/') ? url.substring(0, url.length - 1) : url;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_serverUrlKey, _baseUrl);
  }

  /// Headers helper
  Map<String, String> _headers({bool requiresAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (requiresAuth && _token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }

  // ==========================================
  // 1. AUTHENTICATION ENDPOINTS
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> login(String loginId, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/auth/login'),
        headers: _headers(requiresAuth: false),
        body: jsonEncode({
          'email': loginId,
          'login': loginId,
          'password': password,
        }),
      ).timeout(const Duration(seconds: 12));

      dynamic data;
      try {
        data = jsonDecode(response.body);
      } catch (_) {
        data = {'message': response.body};
      }

      final isSuccess = response.statusCode >= 200 && response.statusCode < 300 &&
          (data is Map && (data['success'] == true || data['status'] == 'success' || data['status'] == true || data['token'] != null));

      if (isSuccess && data is Map<String, dynamic>) {
        _token = data['token'] ?? data['data']?['token'] ?? data['access_token'];
        if (_token != null) {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(_tokenKey, _token!);
          _sessionTimestamp = DateTime.now().millisecondsSinceEpoch;
          await prefs.setInt(_sessionTimestampKey, _sessionTimestamp!);
          if (data['user'] != null || data['data']?['user'] != null) {
            await prefs.setString(_teacherDataKey, jsonEncode(data['user'] ?? data['data']?['user']));
          }
        }
        return ApiResponse(
          success: true,
          message: data['message']?.toString() ?? 'Login successful',
          data: data,
          statusCode: response.statusCode,
        );
      } else {
        final msg = data is Map ? (data['message'] ?? data['error'] ?? 'Authentication failed') : 'Authentication failed';
        return ApiResponse(
          success: false,
          message: msg.toString(),
          data: data is Map<String, dynamic> ? data : null,
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Network error: Unable to reach school server ($e)',
        statusCode: 500,
      );
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> getAuthMe() async {
    return _get<Map<String, dynamic>>('$_baseUrl/auth/me');
  }

  Future<ApiResponse<bool>> logout() async {
    try {
      if (_token != null) {
        await http.post(
          Uri.parse('$_baseUrl/auth/logout'),
          headers: _headers(),
        ).timeout(const Duration(seconds: 6));
      }
    } catch (_) {}

    _token = null;
    _sessionTimestamp = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_teacherDataKey);
    await prefs.remove(_sessionTimestampKey);

    return ApiResponse(success: true, message: 'Logged out successfully', statusCode: 200);
  }

  Future<ApiResponse<dynamic>> forgotPassword(String email) async {
    try {
      final res = await http.post(
        Uri.parse('$_baseUrl/auth/forgot-password'),
        headers: _headers(requiresAuth: false),
        body: jsonEncode({'email': email}),
      );
      final data = jsonDecode(res.body);
      return ApiResponse(
        success: res.statusCode == 200,
        message: data['message'] ?? 'Reset instructions sent',
        data: data,
        statusCode: res.statusCode,
      );
    } catch (e) {
      return ApiResponse(success: false, message: '$e', statusCode: 500);
    }
  }

  // ==========================================
  // 2. DASHBOARD ENDPOINTS
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getDashboardStats() async {
    return _get<Map<String, dynamic>>('$_baseUrl/dashboard/stats');
  }

  Future<ApiResponse<Map<String, dynamic>>> getNextClass() async {
    return _get<Map<String, dynamic>>('$_baseUrl/dashboard/next-class');
  }

  Future<ApiResponse<List<dynamic>>> getDashboardTimeline() async {
    return _get<List<dynamic>>('$_baseUrl/dashboard/timeline');
  }

  Future<ApiResponse<List<dynamic>>> getPendingAssignments() async {
    return _get<List<dynamic>>('$_baseUrl/dashboard/pending-assignments');
  }

  Future<ApiResponse<Map<String, dynamic>>> getUpcomingExam() async {
    return _get<Map<String, dynamic>>('$_baseUrl/dashboard/upcoming-exam');
  }

  Future<ApiResponse<List<dynamic>>> getLowAttendanceAlerts() async {
    return _get<List<dynamic>>('$_baseUrl/dashboard/low-attendance');
  }

  Future<ApiResponse<List<dynamic>>> getTeachingTips() async {
    return _get<List<dynamic>>('$_baseUrl/teaching-tips');
  }

  // ==========================================
  // 3. CLASSES & CLASS TEACHER
  // ==========================================

  Future<ApiResponse<List<dynamic>>> getClassesSummary() async {
    return _get<List<dynamic>>('$_baseUrl/classes/summary');
  }

  Future<ApiResponse<Map<String, dynamic>>> getClassDetail(String classId) async {
    return _get<Map<String, dynamic>>('$_baseUrl/classes/$classId');
  }

  Future<ApiResponse<List<dynamic>>> getClassSubjects(int classId, {int? semesterId}) async {
    final q = semesterId != null ? '?semester_id=$semesterId' : '';
    return _get<List<dynamic>>('$_baseUrl/classes/$classId/subjects$q');
  }

  Future<ApiResponse<List<dynamic>>> getTopStudents(String classId) async {
    return _get<List<dynamic>>('$_baseUrl/classes/$classId/top-students');
  }

  Future<ApiResponse<Map<String, dynamic>>> getClassTeacherOverview() async {
    return _get<Map<String, dynamic>>('$_baseUrl/class-teacher/overview');
  }

  Future<ApiResponse<List<dynamic>>> getClassTeacherStudents() async {
    return _get<List<dynamic>>('$_baseUrl/class-teacher/students');
  }

  Future<ApiResponse<List<dynamic>>> getClassTeacherLeaves() async {
    return _get<List<dynamic>>('$_baseUrl/class-teacher/leaves');
  }

  Future<ApiResponse<dynamic>> handleLeaveAction(int leaveId, String status, {String? remark}) async {
    return _post('$_baseUrl/class-teacher/leaves/$leaveId/action', {
      'status': status,
      if (remark != null) 'remark': remark,
    });
  }

  Future<ApiResponse<dynamic>> postClassDiary(Map<String, dynamic> diaryData) async {
    return _post('$_baseUrl/class-teacher/diary', diaryData);
  }

  // ==========================================
  // 4. STUDENTS
  // ==========================================

  Future<ApiResponse<List<dynamic>>> getStudents({String? classId, String? search}) async {
    String url = '$_baseUrl/students';
    final params = <String>[];
    if (classId != null) params.add('class_id=$classId');
    if (search != null && search.isNotEmpty) params.add('search=${Uri.encodeComponent(search)}');
    if (params.isNotEmpty) url += '?${params.join('&')}';
    return _get<List<dynamic>>(url);
  }

  Future<ApiResponse<Map<String, dynamic>>> getStudentDetail(String studentId) async {
    return _get<Map<String, dynamic>>('$_baseUrl/students/$studentId');
  }

  Future<ApiResponse<Map<String, dynamic>>> getStudentPerformance(String studentId) async {
    return _get<Map<String, dynamic>>('$_baseUrl/students/$studentId/performance');
  }

  // ==========================================
  // 5. ATTENDANCE
  // ==========================================

  Future<ApiResponse<List<dynamic>>> getAttendanceClasses() async {
    return _get<List<dynamic>>('$_baseUrl/attendance/classes');
  }

  Future<ApiResponse<List<dynamic>>> getClassStudentsForAttendance(String classId) async {
    return _get<List<dynamic>>('$_baseUrl/attendance/$classId/students');
  }

  Future<ApiResponse<Map<String, dynamic>>> getAttendanceByDate(String classId, String date) async {
    return _get<Map<String, dynamic>>('$_baseUrl/attendance/$classId/date/$date');
  }

  Future<ApiResponse<dynamic>> submitAttendance(String classId, Map<String, dynamic> payload) async {
    return _post('$_baseUrl/attendance/$classId', payload);
  }

  // ==========================================
  // 6. SCHEDULE & TIMETABLE (रूटीन तथा समय तालिका)
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getWeeklySchedule() async {
    return _get<Map<String, dynamic>>('$_baseUrl/schedule/weekly');
  }

  Future<ApiResponse<List<dynamic>>> getDailySchedule(String date) async {
    return _get<List<dynamic>>('$_baseUrl/schedule/daily/$date');
  }

  Future<ApiResponse<Map<String, dynamic>>> getClassRoutine({int? classId, int? sectionId}) async {
    String url = '$_baseUrl/schedule/class-routine';
    final queryParams = <String>[];
    if (classId != null) queryParams.add('class_id=$classId');
    if (sectionId != null) queryParams.add('section_id=$sectionId');
    if (queryParams.isNotEmpty) {
      url += '?${queryParams.join('&')}';
    }
    return _get<Map<String, dynamic>>(url);
  }

  Future<ApiResponse<Map<String, dynamic>>> getAvailableTeachers() async {
    return _get<Map<String, dynamic>>('$_baseUrl/schedule/available-teachers');
  }

  Future<ApiResponse<dynamic>> requestSubstitution(Map<String, dynamic> data) async {
    return _post('$_baseUrl/schedule/substitution-request', data);
  }

  Future<ApiResponse<Map<String, dynamic>>> getSubstitutions() async {
    return _get<Map<String, dynamic>>('$_baseUrl/schedule/substitutions');
  }

  // ==========================================
  // 7. ASSIGNMENTS & HOMEWORK
  // ==========================================

  Future<ApiResponse<List<dynamic>>> getAssignments({String? classId}) async {
    String url = '$_baseUrl/assignments';
    if (classId != null) url += '?class_id=$classId';
    return _get<List<dynamic>>(url);
  }

  Future<ApiResponse<dynamic>> createAssignment(Map<String, dynamic> data) async {
    return _post('$_baseUrl/assignments', data);
  }

  Future<ApiResponse<Map<String, dynamic>>> getAssignmentDetail(String id) async {
    return _get<Map<String, dynamic>>('$_baseUrl/assignments/$id');
  }

  Future<ApiResponse<List<dynamic>>> getAssignmentSubmissions(String id) async {
    return _get<List<dynamic>>('$_baseUrl/assignments/$id/submissions');
  }

  Future<ApiResponse<dynamic>> gradeHomeworkSubmission(String homeworkId, String submissionId, double marks, {String? feedback}) async {
    return _post('$_baseUrl/assignments/$homeworkId/submissions/$submissionId/grade', {
      'marks_obtained': marks,
      'feedback': feedback ?? '',
    });
  }

  Future<ApiResponse<dynamic>> remindHomeworkDefaulters(String homeworkId) async {
    return _post('$_baseUrl/assignments/$homeworkId/remind', {});
  }

  Future<ApiResponse<dynamic>> deleteAssignment(String id) async {
    return _delete('$_baseUrl/assignments/$id');
  }

  // ==========================================
  // LESSON PLANNING & SYLLABUS (पाठ योजना)
  // ==========================================

  Future<ApiResponse<List<dynamic>>> getLessonPlans({String? date, String? classId}) async {
    String url = '$_baseUrl/lesson-plans';
    final query = <String>[];
    if (date != null) query.add('date=$date');
    if (classId != null) query.add('class_id=$classId');
    if (query.isNotEmpty) url += '?${query.join('&')}';
    return _get<List<dynamic>>(url);
  }

  Future<ApiResponse<dynamic>> createLessonPlan(Map<String, dynamic> data) async {
    return _post('$_baseUrl/lesson-plans', data);
  }

  Future<ApiResponse<Map<String, dynamic>>> getLessonPlanDetail(String id) async {
    return _get<Map<String, dynamic>>('$_baseUrl/lesson-plans/detail/$id');
  }

  Future<ApiResponse<dynamic>> updateLessonPlan(String id, Map<String, dynamic> data) async {
    return _put('$_baseUrl/lesson-plans/$id', data);
  }

  Future<ApiResponse<dynamic>> markLessonPlanCompleted(String id) async {
    return _post('$_baseUrl/lesson-plans/$id/complete', {});
  }

  Future<ApiResponse<dynamic>> deleteLessonPlan(String id) async {
    return _delete('$_baseUrl/lesson-plans/$id');
  }

  // ==========================================
  // 8. GRADEBOOK & EXAMS (ग्रेडसिट तथा प्राप्ताङ्क)
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getGradebook(String classId, {String? examId, String? sectionId}) async {
    String url = '$_baseUrl/gradebook/$classId';
    final q = <String>[];
    if (examId != null) q.add('exam_id=$examId');
    if (sectionId != null) q.add('section_id=$sectionId');
    if (q.isNotEmpty) url += '?${q.join('&')}';
    return _get<Map<String, dynamic>>(url);
  }

  Future<ApiResponse<Map<String, dynamic>>> exportGradebook(String classId) async {
    return _get<Map<String, dynamic>>('$_baseUrl/gradebook/$classId/export');
  }

  Future<ApiResponse<Map<String, dynamic>>> getStudentGradeHistory(String studentId) async {
    return _get<Map<String, dynamic>>('$_baseUrl/gradebook/$studentId/history');
  }

  Future<ApiResponse<dynamic>> updateStudentGrade(String studentId, String assignmentId, double marks, {String? remark}) async {
    return _put('$_baseUrl/gradebook/$studentId/$assignmentId', {
      'marks': marks,
      'teacher_remarks': remark ?? '',
    });
  }

  Future<ApiResponse<List<dynamic>>> getExams() async {
    return _get<List<dynamic>>('$_baseUrl/exams');
  }

  Future<ApiResponse<List<dynamic>>> getExamMarks({
    String? examId,
    int? classId,
    int? sectionId,
    int? courseId,
  }) async {
    final queryParams = <String>[];
    if (examId != null) queryParams.add('exam_id=$examId');
    if (classId != null) queryParams.add('class_id=$classId');
    if (sectionId != null) queryParams.add('section_id=$sectionId');
    if (courseId != null) queryParams.add('course_id=$courseId');
    final queryStr = queryParams.isNotEmpty ? '?${queryParams.join('&')}' : '';
    return _get<List<dynamic>>('$_baseUrl/exams/marks$queryStr');
  }

  Future<ApiResponse<dynamic>> submitExamMarks(String examId, Map<String, dynamic> marksData) async {
    return _put('$_baseUrl/exams/$examId/marks', marksData);
  }

  // ==========================================
  // 9. AI QUESTION PAPERS
  // ==========================================

  Future<ApiResponse<List<dynamic>>> getQuestionPapers() async {
    return _get<List<dynamic>>('$_baseUrl/question-papers');
  }

  Future<ApiResponse<dynamic>> autoCreateQuestionPaper(Map<String, dynamic> params) async {
    return _post('$_baseUrl/question-papers/auto-create', params);
  }

  // ==========================================
  // 10. NOTIFICATIONS, ANNOUNCEMENTS & TODOS
  // ==========================================

  Future<ApiResponse<List<dynamic>>> getNotifications() async {
    return _get<List<dynamic>>('$_baseUrl/notifications');
  }

  Future<ApiResponse<dynamic>> markAllNotificationsRead() async {
    return _put('$_baseUrl/notifications/read-all', {});
  }

  Future<ApiResponse<List<dynamic>>> getAnnouncements() async {
    return _get<List<dynamic>>('$_baseUrl/announcements');
  }

  Future<ApiResponse<List<dynamic>>> getTodos() async {
    return _get<List<dynamic>>('$_baseUrl/todos');
  }

  Future<ApiResponse<dynamic>> toggleTodo(int todoId) async {
    return _put('$_baseUrl/todos/$todoId/toggle', {});
  }

  Future<ApiResponse<dynamic>> createTodo(String title, {String? dueDate, String? priority}) async {
    return _post('$_baseUrl/todos', {
      'title': title,
      if (dueDate != null) 'due_date': dueDate,
      if (priority != null) 'priority': priority,
    });
  }

  // ==========================================
  // 11. PROFILE, SECURITY & LEAVES
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getProfile() async {
    return _get<Map<String, dynamic>>('$_baseUrl/teacher/profile');
  }

  Future<ApiResponse<dynamic>> updateProfile(Map<String, dynamic> data) async {
    return _put('$_baseUrl/teacher/profile', data);
  }

  Future<ApiResponse<dynamic>> changePassword(String currentPassword, String newPassword, String confirmPassword) async {
    return _put('$_baseUrl/security/change-password', {
      'current_password': currentPassword,
      'new_password': newPassword,
      'new_password_confirmation': confirmPassword,
    });
  }

  Future<ApiResponse<Map<String, dynamic>>> getLeavesOverview() async {
    return _get<Map<String, dynamic>>('$_baseUrl/leaves');
  }

  Future<ApiResponse<List<dynamic>>> getLeaves() async {
    return _get<List<dynamic>>('$_baseUrl/leaves');
  }

  Future<ApiResponse<dynamic>> applyLeave(Map<String, dynamic> data) async {
    return _post('$_baseUrl/leaves', data);
  }

  Future<ApiResponse<dynamic>> cancelLeave(dynamic id, {String? reason}) async {
    return _put('$_baseUrl/leaves/$id/status', {
      'status': 'cancelled',
      if (reason != null) 'reason': reason,
    });
  }

  Future<ApiResponse<Map<String, dynamic>>> getLeaveDetail(dynamic id) async {
    return _get<Map<String, dynamic>>('$_baseUrl/leaves/$id');
  }

  Future<ApiResponse<List<dynamic>>> getLeaveTypes() async {
    return _get<List<dynamic>>('$_baseUrl/leaves/types');
  }

  Future<ApiResponse<List<dynamic>>> getLeaveSubstitutes() async {
    return _get<List<dynamic>>('$_baseUrl/leaves/substitutes');
  }

  Future<ApiResponse<Map<String, dynamic>>> getSemesterStats() async {
    return _get<Map<String, dynamic>>('$_baseUrl/teacher/semester-stats');
  }

  Future<ApiResponse<Map<String, dynamic>>> getAppVersion() async {
    return _get<Map<String, dynamic>>('$_baseUrl/app-info/version');
  }

  // ==========================================
  // 12. SEATING CHART & BENCH MANAGEMENT
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getSeatingChart(String classId) async {
    return _get<Map<String, dynamic>>('$_baseUrl/seating/$classId');
  }

  Future<ApiResponse<dynamic>> saveSeatingChart(String classId, Map<String, dynamic> data) async {
    return _post('$_baseUrl/seating/$classId', data);
  }

  Future<ApiResponse<dynamic>> randomizeSeatingChart(String classId) async {
    return _post('$_baseUrl/seating/$classId/randomize', {});
  }

  // ==========================================
  // 13. PAYROLL & SALARY MANAGEMENT
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getPayrollOverview() async {
    return _get<Map<String, dynamic>>('$_baseUrl/payroll');
  }

  Future<ApiResponse<List<dynamic>>> getPayslips() async {
    return _get<List<dynamic>>('$_baseUrl/payroll');
  }

  Future<ApiResponse<Map<String, dynamic>>> getPayslipDetail(dynamic id) async {
    return _get<Map<String, dynamic>>('$_baseUrl/payroll/$id');
  }

  Future<ApiResponse<dynamic>> requestSalaryAdvance(Map<String, dynamic> data) async {
    return _post('$_baseUrl/payroll/request-advance', data);
  }

  Future<ApiResponse<Map<String, dynamic>>> getTaxStatement({String? year}) async {
    final query = year != null ? '?year=$year' : '';
    return _get<Map<String, dynamic>>('$_baseUrl/payroll/tax-statement$query');
  }

  // ==========================================
  // 14. PARENT-TEACHER CHAT & MESSAGING
  // ==========================================

  Future<ApiResponse<List<dynamic>>> getChatConversations() async {
    return _get<List<dynamic>>('$_baseUrl/chat/conversations');
  }

  Future<ApiResponse<List<dynamic>>> getConversationMessages(dynamic id) async {
    return _get<List<dynamic>>('$_baseUrl/chat/conversations/$id/messages');
  }

  Future<ApiResponse<dynamic>> sendChatMessage(dynamic conversationId, String text, {String? attachmentUrl}) async {
    return _post('$_baseUrl/chat/conversations/$conversationId/messages', {
      'body': text,
      'text': text,
      if (attachmentUrl != null) 'attachment_url': attachmentUrl,
    });
  }

  Future<ApiResponse<dynamic>> markChatAsRead(dynamic conversationId) async {
    return _put('$_baseUrl/chat/conversations/$conversationId/read', {});
  }

  Future<ApiResponse<List<dynamic>>> getParentContacts() async {
    return _get<List<dynamic>>('$_baseUrl/chat/parent-contacts');
  }

  Future<ApiResponse<dynamic>> startChatConversation(Map<String, dynamic> data) async {
    return _post('$_baseUrl/chat/conversations', data);
  }

  // ==========================================
  // GENERIC HTTP HELPERS
  // ==========================================

  Future<ApiResponse<T>> _get<T>(String url) async {
    if (isSessionExpired()) {
      await logout();
      onSessionExpired?.call();
      return ApiResponse<T>(
        success: false,
        message: 'Your 24-hour session has expired. Please sign in again.',
        statusCode: 401,
      );
    }

    try {
      final response = await http.get(
        Uri.parse(url),
        headers: _headers(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 401) {
        await logout();
        onSessionExpired?.call();
      }

      final data = jsonDecode(response.body);
      final isSuccess = response.statusCode >= 200 && response.statusCode < 300;

      dynamic payload = data;
      if (data is Map) {
        final expectsList = T == List || T.toString().startsWith('List');
        if (expectsList) {
          if (data.containsKey('payslips') && data['payslips'] is List) {
            payload = data['payslips'];
          } else if (data.containsKey('data') && data['data'] is List) {
            payload = data['data'];
          } else if (data.containsKey('classes') && data['classes'] is List) {
            payload = data['classes'];
          } else if (data.containsKey('students') && data['students'] is List) {
            payload = data['students'];
          } else if (data.containsKey('timeline') && data['timeline'] is List) {
            payload = data['timeline'];
          } else if (data.containsKey('assignments') && data['assignments'] is List) {
            payload = data['assignments'];
          } else if (data.containsKey('exams') && data['exams'] is List) {
            payload = data['exams'];
          } else if (data.containsKey('semesters') && data['semesters'] is List) {
            payload = data['semesters'];
          } else if (data.containsKey('terms') && data['terms'] is List) {
            payload = data['terms'];
          } else if (data.containsKey('notifications') && data['notifications'] is List) {
            payload = data['notifications'];
          } else if (data.containsKey('announcements') && data['announcements'] is List) {
            payload = data['announcements'];
          } else if (data.containsKey('todos') && data['todos'] is List) {
            payload = data['todos'];
          } else if (data.containsKey('submissions') && data['submissions'] is List) {
            payload = data['submissions'];
          } else if (data.containsKey('lesson_plans') && data['lesson_plans'] is List) {
            payload = data['lesson_plans'];
          } else if (data.containsKey('leaves') && data['leaves'] is List) {
            payload = data['leaves'];
          } else if (data.containsKey('leave_types') && data['leave_types'] is List) {
            payload = data['leave_types'];
          } else if (data.containsKey('substitutes') && data['substitutes'] is List) {
            payload = data['substitutes'];
          } else if (data.containsKey('conversations') && data['conversations'] is List) {
            payload = data['conversations'];
          } else if (data.containsKey('messages') && data['messages'] is List) {
            payload = data['messages'];
          } else if (data.containsKey('contacts') && data['contacts'] is List) {
            payload = data['contacts'];
          } else if (data.containsKey('marks') && data['marks'] is List) {
            payload = data['marks'];
          } else if (data.containsKey('subjects') && data['subjects'] is List) {
            payload = data['subjects'];
          } else if (data.containsKey('courses') && data['courses'] is List) {
            payload = data['courses'];
          }
        } else {
          if (data.containsKey('data') && data['data'] is Map) {
            payload = data['data'];
          } else if (data.containsKey('stats') && data['stats'] is Map) {
            payload = data['stats'];
          } else if (data.containsKey('next_class') && data['next_class'] is Map) {
            payload = data['next_class'];
          } else if (data.containsKey('overview') && data['overview'] is Map) {
            payload = data['overview'];
          } else if (data.containsKey('payslip') && data['payslip'] is Map) {
            payload = data['payslip'];
          } else if (data.containsKey('leave') && data['leave'] is Map) {
            payload = data['leave'];
          } else if (data.containsKey('conversation') && data['conversation'] is Map) {
            payload = data['conversation'];
          }
        }
      }

      return ApiResponse<T>(
        success: isSuccess,
        message: data is Map ? (data['message']?.toString() ?? 'OK') : 'OK',
        data: isSuccess ? payload as T? : null,
        statusCode: response.statusCode,
      );
    } catch (e) {
      return ApiResponse<T>(
        success: false,
        message: 'Request error: $e',
        statusCode: 500,
      );
    }
  }

  Future<ApiResponse<dynamic>> _post(String url, Map<String, dynamic> body) async {
    if (isSessionExpired()) {
      await logout();
      onSessionExpired?.call();
      return ApiResponse(
        success: false,
        message: 'Your 24-hour session has expired. Please sign in again.',
        statusCode: 401,
      );
    }

    try {
      final response = await http.post(
        Uri.parse(url),
        headers: _headers(),
        body: jsonEncode(body),
      ).timeout(const Duration(seconds: 12));

      if (response.statusCode == 401) {
        await logout();
        onSessionExpired?.call();
      }

      final data = jsonDecode(response.body);
      final isSuccess = response.statusCode >= 200 && response.statusCode < 300;

      return ApiResponse(
        success: isSuccess,
        message: data is Map ? (data['message']?.toString() ?? 'Success') : 'Success',
        data: data,
        statusCode: response.statusCode,
      );
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Post error: $e',
        statusCode: 500,
      );
    }
  }

  Future<ApiResponse<dynamic>> _put(String url, Map<String, dynamic> body) async {
    if (isSessionExpired()) {
      await logout();
      onSessionExpired?.call();
      return ApiResponse(
        success: false,
        message: 'Your 24-hour session has expired. Please sign in again.',
        statusCode: 401,
      );
    }

    try {
      final response = await http.put(
        Uri.parse(url),
        headers: _headers(),
        body: jsonEncode(body),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 401) {
        await logout();
        onSessionExpired?.call();
      }

      final data = jsonDecode(response.body);
      final isSuccess = response.statusCode >= 200 && response.statusCode < 300;

      return ApiResponse(
        success: isSuccess,
        message: data is Map ? (data['message']?.toString() ?? 'Updated successfully') : 'Updated successfully',
        data: data,
        statusCode: response.statusCode,
      );
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Put error: $e',
        statusCode: 500,
      );
    }
  }

  Future<ApiResponse<dynamic>> _delete(String url) async {
    if (isSessionExpired()) {
      await logout();
      onSessionExpired?.call();
      return ApiResponse(
        success: false,
        message: 'Your 24-hour session has expired. Please sign in again.',
        statusCode: 401,
      );
    }

    try {
      final response = await http.delete(
        Uri.parse(url),
        headers: _headers(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 401) {
        await logout();
        onSessionExpired?.call();
      }

      dynamic data;
      try {
        data = jsonDecode(response.body);
      } catch (_) {
        data = {'message': response.body};
      }
      final isSuccess = response.statusCode >= 200 && response.statusCode < 300;

      return ApiResponse(
        success: isSuccess,
        message: data is Map ? (data['message']?.toString() ?? 'Deleted successfully') : 'Deleted successfully',
        data: data,
        statusCode: response.statusCode,
      );
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Delete error: $e',
        statusCode: 500,
      );
    }
  }
}
