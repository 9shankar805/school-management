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

  Future<void> init() async {
    await _apiService.init();
    final prefs = await SharedPreferences.getInstance();
    final cached = prefs.getString('teacher_profile_data');
    if (cached != null) {
      try {
        _teacherProfile = jsonDecode(cached);
      } catch (_) {}
    }
    notifyListeners();
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
}
