import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../data/app_data.dart';

class TeacherProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  bool _loading = false;
  bool get loading => _loading;

  // Live state from real school server
  Map<String, dynamic> _stats = {
    'total_classes': 0,
    'total_students': 0,
    'pending_assignments': 0,
    'unread_messages': 0,
  };
  List<dynamic> _classes = [];
  List<dynamic> _students = [];
  List<dynamic> _assignments = [];
  List<dynamic> _exams = [];
  List<dynamic> _notifications = [];
  List<dynamic> _announcements = [];
  List<dynamic> _timeline = [];
  List<dynamic> _todos = [];
  Map<String, dynamic>? _nextClass;

  Map<String, dynamic> get stats => _stats;
  List<dynamic> get classes => _classes;
  List<dynamic> get students => _students;
  List<dynamic> get assignments => _assignments;
  List<dynamic> get exams => _exams;
  List<dynamic> get notifications => _notifications;
  List<dynamic> get announcements => _announcements;
  List<dynamic> get timeline => _timeline;
  List<dynamic> get todos => _todos;
  Map<String, dynamic>? get nextClass => _nextClass;

  List<TeacherClass> get teacherClasses {
    if (_classes.isNotEmpty) {
      return _classes.asMap().entries.map((e) {
        if (e.value is TeacherClass) return e.value as TeacherClass;
        if (e.value is Map<String, dynamic>) return TeacherClass.fromJson(e.value as Map<String, dynamic>, e.key);
        if (e.value is Map) return TeacherClass.fromJson(Map<String, dynamic>.from(e.value as Map), e.key);
        return AppData.classes[e.key % AppData.classes.length];
      }).toList();
    }
    return AppData.classes;
  }

  List<Student> get studentList {
    if (_students.isNotEmpty) {
      return _students.asMap().entries.map((e) {
        if (e.value is Student) return e.value as Student;
        if (e.value is Map<String, dynamic>) return Student.fromJson(e.value as Map<String, dynamic>, e.key);
        if (e.value is Map) return Student.fromJson(Map<String, dynamic>.from(e.value as Map), e.key);
        return AppData.students[e.key % AppData.students.length];
      }).toList();
    }
    return AppData.students;
  }

  List<TeacherAssignment> get assignmentList {
    if (_assignments.isNotEmpty) {
      return _assignments.asMap().entries.map((e) {
        if (e.value is TeacherAssignment) return e.value as TeacherAssignment;
        if (e.value is Map<String, dynamic>) return TeacherAssignment.fromJson(e.value as Map<String, dynamic>, e.key);
        if (e.value is Map) return TeacherAssignment.fromJson(Map<String, dynamic>.from(e.value as Map), e.key);
        return AppData.assignments[e.key % AppData.assignments.length];
      }).toList();
    }
    return AppData.assignments;
  }

  List<TeacherExam> get examList {
    if (_exams.isNotEmpty) {
      return _exams.asMap().entries.map((e) {
        if (e.value is TeacherExam) return e.value as TeacherExam;
        if (e.value is Map<String, dynamic>) return TeacherExam.fromJson(e.value as Map<String, dynamic>, e.key);
        if (e.value is Map) return TeacherExam.fromJson(Map<String, dynamic>.from(e.value as Map), e.key);
        return AppData.exams[e.key % AppData.exams.length];
      }).toList();
    }
    return AppData.exams;
  }

  int get classesCount => (_stats['total_classes'] ?? _stats['classes_count'] ?? (_classes.isNotEmpty ? _classes.length : 0)) as int? ?? (_classes.isNotEmpty ? _classes.length : 0);
  int get studentsCount => (_stats['total_students'] ?? _stats['students_count'] ?? (_students.isNotEmpty ? _students.length : 0)) as int? ?? (_students.isNotEmpty ? _students.length : 0);
  int get pendingAssignmentsCount => (_stats['pending_assignments'] ?? _assignments.length) as int? ?? _assignments.length;
  int get unreadMessagesCount => (_stats['unread_messages'] ?? 0) as int? ?? 0;

  Future<void> refreshAll() async {
    _loading = true;
    notifyListeners();

    await Future.wait([
      fetchDashboardData(),
      fetchClasses(),
      fetchStudents(),
      fetchTimeline(),
      fetchAssignments(),
      fetchExams(),
      fetchNotifications(),
      fetchAnnouncements(),
      fetchTodos(),
    ]);

    _loading = false;
    notifyListeners();
  }

  Future<void> fetchDashboardData() async {
    try {
      final res = await _apiService.getDashboardStats();
      if (res.success && res.data != null) {
        final d = res.data!;
        _stats = d is Map<String, dynamic> ? (d['stats'] is Map<String, dynamic> ? d['stats'] : d) : _stats;
      }
      final nextRes = await _apiService.getNextClass();
      if (nextRes.success && nextRes.data != null) {
        final nd = nextRes.data!;
        _nextClass = nd is Map<String, dynamic> ? (nd['next_class'] is Map<String, dynamic> ? nd['next_class'] : nd) : null;
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> fetchClasses() async {
    try {
      final res = await _apiService.getClassesSummary();
      if (res.success && res.data != null) {
        if (res.data is List) {
          _classes = res.data as List;
        } else if (res.data is Map && res.data['classes'] is List) {
          _classes = res.data['classes'];
        }
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> fetchStudents() async {
    try {
      final res = await _apiService.getStudents();
      if (res.success && res.data != null) {
        if (res.data is List) {
          _students = res.data as List;
        } else if (res.data is Map && res.data['students'] is List) {
          _students = res.data['students'];
        }
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> fetchTimeline() async {
    try {
      final res = await _apiService.getDashboardTimeline();
      if (res.success && res.data != null) {
        if (res.data is List) {
          _timeline = res.data as List;
        } else if (res.data is Map && res.data['timeline'] is List) {
          _timeline = res.data['timeline'];
        }
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> fetchAssignments() async {
    try {
      final res = await _apiService.getAssignments();
      if (res.success && res.data != null) {
        if (res.data is List) {
          _assignments = res.data as List;
        } else if (res.data is Map && res.data['assignments'] is List) {
          _assignments = res.data['assignments'];
        }
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> fetchExams() async {
    try {
      final res = await _apiService.getExams();
      if (res.success && res.data != null) {
        if (res.data is List) {
          _exams = res.data as List;
        } else if (res.data is Map && res.data['exams'] is List) {
          _exams = res.data['exams'];
        }
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> fetchNotifications() async {
    try {
      final res = await _apiService.getNotifications();
      if (res.success && res.data != null) {
        if (res.data is List) {
          _notifications = res.data as List;
        } else if (res.data is Map && res.data['notifications'] is List) {
          _notifications = res.data['notifications'];
        }
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> fetchAnnouncements() async {
    try {
      final res = await _apiService.getAnnouncements();
      if (res.success && res.data != null) {
        if (res.data is List) {
          _announcements = res.data as List;
        } else if (res.data is Map && res.data['announcements'] is List) {
          _announcements = res.data['announcements'];
        }
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> fetchTodos() async {
    try {
      final res = await _apiService.getTodos();
      if (res.success && res.data != null) {
        if (res.data is List) {
          _todos = res.data as List;
        } else if (res.data is Map && res.data['todos'] is List) {
          _todos = res.data['todos'];
        }
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<bool> submitAttendance(String classId, Map<String, dynamic> payload) async {
    final res = await _apiService.submitAttendance(classId, payload);
    return res.success;
  }
}
