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

  String _baseUrl = _defaultBaseUrl;
  String? _token;

  String get baseUrl => _baseUrl;
  String? get token => _token;
  bool get isAuthenticated => _token != null && _token!.isNotEmpty;

  /// Initialize API service and load cached token / server preferences
  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
    final customUrl = prefs.getString(_serverUrlKey);
    if (customUrl != null && customUrl.isNotEmpty) {
      _baseUrl = customUrl;
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
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_teacherDataKey);

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
  // 6. SCHEDULE & TIMETABLE
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getWeeklySchedule() async {
    return _get<Map<String, dynamic>>('$_baseUrl/schedule/weekly');
  }

  Future<ApiResponse<List<dynamic>>> getDailySchedule(String date) async {
    return _get<List<dynamic>>('$_baseUrl/schedule/daily/$date');
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

  // ==========================================
  // 8. GRADEBOOK & EXAMS
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getGradebook(String classId) async {
    return _get<Map<String, dynamic>>('$_baseUrl/gradebook/$classId');
  }

  Future<ApiResponse<dynamic>> updateStudentGrade(String studentId, String assignmentId, double marks) async {
    return _put('$_baseUrl/gradebook/$studentId/$assignmentId', {'marks': marks});
  }

  Future<ApiResponse<List<dynamic>>> getExams() async {
    return _get<List<dynamic>>('$_baseUrl/exams');
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
  // 11. PROFILE & APP INFO
  // ==========================================

  Future<ApiResponse<Map<String, dynamic>>> getProfile() async {
    return _get<Map<String, dynamic>>('$_baseUrl/teacher/profile');
  }

  Future<ApiResponse<Map<String, dynamic>>> getAppVersion() async {
    return _get<Map<String, dynamic>>('$_baseUrl/app-info/version');
  }

  // ==========================================
  // GENERIC HTTP HELPERS
  // ==========================================

  Future<ApiResponse<T>> _get<T>(String url) async {
    try {
      final response = await http.get(
        Uri.parse(url),
        headers: _headers(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      final isSuccess = response.statusCode >= 200 && response.statusCode < 300;

      dynamic payload = data;
      if (data is Map) {
        if (data.containsKey('data')) {
          payload = data['data'];
        } else if (data.containsKey('classes')) {
          payload = data['classes'];
        } else if (data.containsKey('students')) {
          payload = data['students'];
        } else if (data.containsKey('timeline')) {
          payload = data['timeline'];
        } else if (data.containsKey('assignments')) {
          payload = data['assignments'];
        } else if (data.containsKey('exams')) {
          payload = data['exams'];
        } else if (data.containsKey('notifications')) {
          payload = data['notifications'];
        } else if (data.containsKey('announcements')) {
          payload = data['announcements'];
        } else if (data.containsKey('todos')) {
          payload = data['todos'];
        } else if (data.containsKey('stats')) {
          payload = data['stats'];
        } else if (data.containsKey('next_class')) {
          payload = data['next_class'];
        } else if (data.containsKey('overview')) {
          payload = data['overview'];
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
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: _headers(),
        body: jsonEncode(body),
      ).timeout(const Duration(seconds: 12));

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
    try {
      final response = await http.put(
        Uri.parse(url),
        headers: _headers(),
        body: jsonEncode(body),
      ).timeout(const Duration(seconds: 10));

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
}
