import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  bool _isLoading = false;
  String? _errorMessage;
  Map<String, dynamic>? _teacherProfile;

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  Map<String, dynamic>? get teacherProfile => _teacherProfile;
  bool get isAuthenticated => _apiService.isAuthenticated;
  bool get isSessionExpired => _apiService.isSessionExpired();
  Duration get sessionRemainingTime => _apiService.remainingSessionTime;
  DateTime? get sessionExpiresAt => _apiService.sessionExpiresAt;

  String get sessionRemainingFormatted {
    if (!_apiService.isAuthenticated) return 'Signed Out';
    final rem = _apiService.remainingSessionTime;
    if (rem.inSeconds <= 0) return 'Expired (सत्र समाप्त)';
    if (rem.inHours > 0) {
      final m = rem.inMinutes.remainder(60);
      return '${rem.inHours}h ${m}m remaining';
    }
    return '${rem.inMinutes}m remaining';
  }

  String get teacherName {
    if (_teacherProfile?['name'] != null && _teacherProfile!['name'].toString().isNotEmpty) {
      return _teacherProfile!['name'].toString();
    }
    if (_teacherProfile?['full_name'] != null && _teacherProfile!['full_name'].toString().isNotEmpty) {
      return _teacherProfile!['full_name'].toString();
    }
    if (_teacherProfile?['email'] != null && _teacherProfile!['email'].toString().isNotEmpty) {
      final em = _teacherProfile!['email'].toString();
      final username = em.split('@').first;
      return username.isNotEmpty ? username[0].toUpperCase() + username.substring(1) : 'Teacher';
    }
    return 'Teacher';
  }

  String get teacherEmail => _teacherProfile?['email']?.toString() ?? '';
  String get teacherDesignation => _teacherProfile?['designation']?.toString() ?? 'Faculty Member';

  String get schoolName {
    final school = _teacherProfile?['school'];
    if (school is Map && school['name'] != null && school['name'].toString().isNotEmpty) {
      return school['name'].toString();
    }
    if (_teacherProfile?['school_name'] != null && _teacherProfile!['school_name'].toString().isNotEmpty) {
      return _teacherProfile!['school_name'].toString();
    }
    if (_teacherProfile?['institution_name'] != null && _teacherProfile!['institution_name'].toString().isNotEmpty) {
      return _teacherProfile!['institution_name'].toString();
    }
    return 'Nepal School Hub (NSHLMS)';
  }

  String? get schoolLogo {
    final school = _teacherProfile?['school'];
    if (school is Map) {
      final logo = school['logo'] ?? school['logo_url'] ?? school['image'] ?? school['school_logo'];
      if (logo != null && logo.toString().isNotEmpty) return logo.toString();
    }
    final logo = _teacherProfile?['school_logo'] ??
                 _teacherProfile?['school_logo_url'] ??
                 _teacherProfile?['logo_url'] ??
                 _teacherProfile?['school_image'];
    if (logo != null && logo.toString().isNotEmpty) return logo.toString();
    return null;
  }

  String get schoolAddress {
    final school = _teacherProfile?['school'];
    if (school is Map && school['address'] != null) {
      return school['address'].toString();
    }
    return _teacherProfile?['school_address']?.toString() ?? 'Nepal';
  }

  String get teacherAvatar => _teacherProfile?['avatar_url']?.toString() ?? '';
  String get teacherPhone => _teacherProfile?['phone']?.toString() ?? '';
  String get teacherAddress => _teacherProfile?['address']?.toString() ?? '';
  String get teacherCity => _teacherProfile?['city']?.toString() ?? '';
  String get teacherId => _teacherProfile?['id']?.toString() ?? '2081-01';

  bool get isAdminRole {
    final roles = _teacherProfile?['roles'];
    if (roles is List) {
      return roles.any((r) => [
            'admin',
            'super-admin',
            'principal',
            'vice-principal',
            'academic-coordinator'
          ].contains(r.toString().toLowerCase().trim()));
    }
    return false;
  }

  bool get isClassTeacherRole {
    final roles = _teacherProfile?['roles'];
    if (roles is List) {
      return roles.any((r) =>
          r.toString().toLowerCase().contains('class-teacher') ||
          r.toString().toLowerCase().contains('incharge'));
    }
    return _teacherProfile?['is_class_teacher'] == true ||
        _teacherProfile?['is_class_teacher'] == 1 ||
        _teacherProfile?['is_class_teacher'] == '1';
  }

  Future<void> init() async {
    ApiService.onSessionExpired = () {
      _teacherProfile = null;
      _errorMessage = 'Your 24-hour session expired. Please sign in again.';
      notifyListeners();
    };

    await _apiService.init();

    if (_apiService.isSessionExpired()) {
      _teacherProfile = null;
      _errorMessage = 'Your 24-hour session has expired for security. Please sign in again.';
      notifyListeners();
      return;
    }

    final prefs = await SharedPreferences.getInstance();
    final cached = prefs.getString('teacher_profile_data');
    if (cached != null) {
      try {
        _teacherProfile = jsonDecode(cached);
      } catch (_) {}
    }
    notifyListeners();
  }

  /// Check active 24-hour session status
  bool checkSession() {
    if (_apiService.isSessionExpired()) {
      _teacherProfile = null;
      _errorMessage = 'Your 24-hour session has expired for security. Please sign in again.';
      notifyListeners();
      return false;
    }
    return true;
  }

  Future<bool> login(String loginId, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _apiService.login(loginId, password);

    if (result.success && result.data != null) {
      final rawData = result.data!;
      final userObj = rawData['user'] ?? rawData['data']?['user'] ?? rawData['profile'] ?? rawData['teacher'];
      if (userObj is Map<String, dynamic>) {
        _teacherProfile = Map<String, dynamic>.from(userObj);
      } else {
        final username = loginId.contains('@') ? loginId.split('@').first : loginId;
        final cleanName = username.isNotEmpty ? username[0].toUpperCase() + username.substring(1) : 'Teacher';
        _teacherProfile = {
          'name': cleanName,
          'email': loginId,
          'designation': 'Senior Subject Teacher',
        };
      }

      // Fetch extra user details if available
      try {
        final meRes = await _apiService.getAuthMe();
        if (meRes.success && meRes.data != null) {
          _teacherProfile!.addAll(meRes.data!);
          if (meRes.data!['first_name'] != null && meRes.data!['last_name'] != null) {
            _teacherProfile!['name'] = '${meRes.data!['first_name']} ${meRes.data!['last_name']}';
          }
        }
      } catch (_) {}

      // Ensure school details & logo
      if (_teacherProfile!['school'] == null) {
        _teacherProfile!['school'] = {
          'name': 'Nepal School Hub (NSHLMS)',
          'code': 'NSH-2081',
          'logo': 'https://nshlms.com/imgs/logo_icon.webp',
          'address': 'Kathmandu, Lalitpur, Nepal',
        };
      }
      if (_teacherProfile!['school_logo'] == null) {
        _teacherProfile!['school_logo'] = 'https://nshlms.com/imgs/logo_icon.webp';
      }
      if (_teacherProfile!['school_name'] == null) {
        _teacherProfile!['school_name'] = 'Nepal School Hub (NSHLMS)';
      }
      if (_teacherProfile!['designation'] == null) {
        final roles = _teacherProfile!['roles'];
        if (roles is List && roles.isNotEmpty) {
          _teacherProfile!['designation'] = roles.map((r) => r.toString().toUpperCase().replaceAll('-', ' ')).join(' • ');
        } else {
          _teacherProfile!['designation'] = 'Faculty • Nepal School Hub';
        }
      }

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('teacher_profile_data', jsonEncode(_teacherProfile));
      _isLoading = false;
      notifyListeners();
      return true;
    } else {
      _isLoading = false;
      _errorMessage = result.message;
      notifyListeners();
      return false;
    }
  }

  /// Instant offline demo login for testing and when server is unreachable
  Future<bool> loginInDemoMode([String? loginId]) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final email = (loginId != null && loginId.isNotEmpty) ? loginId : 'kunti.yadav@school.edu';
    final parts = email.split('@').first.split('.');
    final name = parts.map((p) => p.isNotEmpty ? p[0].toUpperCase() + p.substring(1) : '').join(' ');

    _teacherProfile = {
      'id': 'NSH-T-2081',
      'name': name.isNotEmpty ? name : 'Kunti Yadav',
      'email': email,
      'phone': '+977 9841234567',
      'designation': 'Senior Faculty / Class Teacher',
      'is_class_teacher': true,
      'roles': ['teacher', 'class-teacher'],
      'school': {
        'name': 'Nepal School Hub (NSHLMS)',
        'code': 'NSH-2081',
        'address': 'Kathmandu, Lalitpur, Nepal',
      },
      'school_name': 'Nepal School Hub (NSHLMS)',
      'school_logo': 'assets/images/logo.png',
    };

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('teacher_profile_data', jsonEncode(_teacherProfile));
    await prefs.setString('auth_token', 'demo_offline_token_${DateTime.now().millisecondsSinceEpoch}');
    await prefs.setInt('session_login_timestamp', DateTime.now().millisecondsSinceEpoch);

    _isLoading = false;
    notifyListeners();
    return true;
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();
    await _apiService.logout();
    _teacherProfile = null;
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchProfile() async {
    final meRes = await _apiService.getAuthMe();
    if (meRes.success && meRes.data != null) {
      if (_teacherProfile != null) {
        _teacherProfile!.addAll(meRes.data!);
      } else {
        _teacherProfile = Map<String, dynamic>.from(meRes.data!);
      }
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('teacher_profile_data', jsonEncode(_teacherProfile));
      notifyListeners();
    }
  }

  Future<bool> updateProfile(Map<String, dynamic> data) async {
    _isLoading = true;
    notifyListeners();
    final res = await _apiService.updateProfile(data);
    _isLoading = false;
    if (res.success) {
      if (_teacherProfile != null) {
        _teacherProfile!.addAll(data);
        if (data.containsKey('first_name') || data.containsKey('last_name')) {
          final fn = data['first_name'] ?? _teacherProfile!['first_name'] ?? '';
          final ln = data['last_name'] ?? _teacherProfile!['last_name'] ?? '';
          _teacherProfile!['name'] = '$fn $ln'.trim();
        }
      }
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('teacher_profile_data', jsonEncode(_teacherProfile));
      notifyListeners();
      return true;
    }
    notifyListeners();
    return false;
  }

  Future<ApiResponse<dynamic>> changePassword(String currentPassword, String newPassword, String confirmPassword) async {
    return _apiService.changePassword(currentPassword, newPassword, confirmPassword);
  }

  Future<List<dynamic>> getLeaves() async {
    final res = await _apiService.getLeaves();
    if (res.success && res.data != null) {
      return res.data!;
    }
    return [];
  }

  Future<ApiResponse<dynamic>> applyLeave(Map<String, dynamic> data) async {
    return _apiService.applyLeave(data);
  }
}
