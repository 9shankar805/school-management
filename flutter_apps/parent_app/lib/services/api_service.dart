import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../data/app_data.dart';

class ApiService {
  static const String baseUrl = 'https://nepalschoolhub.techprocod.com.np/api';

  static String? _token;
  static final http.Client _client = http.Client();
  static final Map<String, dynamic> _cache = {};
  static final Map<String, DateTime> _cacheTime = {};

  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
  }

  static String? get token => _token;

  static void clearCache() {
    _cache.clear();
    _cacheTime.clear();
  }

  static Future<Map<String, String>> _getHeaders() async {
    if (_token == null) await init();
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (_token != null && _token!.isNotEmpty)
        'Authorization': 'Bearer $_token',
    };
  }

  static dynamic _extractData(http.Response response) {
    if (response.statusCode >= 200 && response.statusCode < 300) {
      final body = jsonDecode(response.body);
      if (body is Map<String, dynamic>) {
        if (body.containsKey('data')) return body['data'];
        return body;
      }
      return body;
    }
    return null;
  }

  // ==========================================================================
  // 1. AUTHENTICATION MODULE
  // ==========================================================================
  static Future<Map<String, dynamic>> login(String email, String password) async {
    clearCache();
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/auth/login'),
        headers: await _getHeaders(),
        body: jsonEncode({'email': email, 'password': password}),
      );

      final body = jsonDecode(response.body);
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (body['token'] != null || body['access_token'] != null) {
          _token = body['token'] ?? body['access_token'];
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('auth_token', _token!);
        }
        return {'success': true, 'data': body};
      } else {
        return {
          'success': false,
          'message': body['message'] ?? 'Invalid credentials. Please try again.'
        };
      }
    } catch (e) {
      return {'success': false, 'message': 'Network connection error: $e'};
    }
  }

  static Future<Map<String, dynamic>?> getProfile({bool forceRefresh = false}) async {
    const key = 'profile';
    if (!forceRefresh && _cache.containsKey(key) && _cacheTime.containsKey(key)) {
      if (DateTime.now().difference(_cacheTime[key]!).inSeconds < 45) {
        return _cache[key] as Map<String, dynamic>?;
      }
    }
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/auth/me'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data != null && data is Map<String, dynamic>) {
          _cache[key] = data;
          _cacheTime[key] = DateTime.now();
          return data;
        }
      }
    } catch (_) {}
    return null;
  }

  static Future<bool> updateProfile(Map<String, dynamic> data) async {
    try {
      final response = await _client.put(
        Uri.parse('$baseUrl/auth/profile'),
        headers: await _getHeaders(),
        body: jsonEncode(data),
      );
      if (response.statusCode == 200) {
        clearCache();
        return true;
      }
    } catch (_) {}
    return false;
  }

  static Future<bool> forgotPassword(String email) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/auth/forgot-password'),
        headers: await _getHeaders(),
        body: jsonEncode({'email': email}),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<void> logout() async {
    try {
      await _client.post(
        Uri.parse('$baseUrl/auth/logout'),
        headers: await _getHeaders(),
      );
    } catch (_) {}
    _token = null;
    clearCache();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  // ==========================================================================
  // 2. CHILDREN MODULE
  // ==========================================================================
  static Future<List<Child>> getChildren() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/children'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List) {
          return data.map((item) => Child.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return AppData.children;
  }

  static Future<Child?> getChild(int id) async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/children/$id'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is Map<String, dynamic>) return Child.fromJson(data);
      }
    } catch (_) {}
    return null;
  }

  static Future<Map<String, dynamic>?> getChildOverview(int id) async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/children/$id/overview'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is Map<String, dynamic>) return data;
      }
    } catch (_) {}
    return null;
  }

  // ==========================================================================
  // 3. ACADEMIC PERFORMANCE & REPORT CARDS
  // ==========================================================================
  static Future<List<SubjectGrade>> getSubjects({int? childId}) async {
    try {
      final url = childId != null
          ? '$baseUrl/grades/child/$childId'
          : '$baseUrl/grades';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => SubjectGrade.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return AppData.subjects;
  }

  static Future<List<ReportCard>> getReportCards({int? childId}) async {
    try {
      final url = childId != null
          ? '$baseUrl/report-cards/child/$childId'
          : '$baseUrl/report-cards';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => ReportCard.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      ReportCard(
        id: 1,
        term: 'Second Terminal Examination 2081',
        academicYear: '2081-2082 (2026)',
        issueDate: '2026-08-15',
        overallGrade: 'A+',
        principalRemarks: 'Outstanding intellectual performance with exemplary discipline.',
        gpa: 3.9,
        percentage: 91.5,
        totalMarks: 700,
        obtainedMarks: 641,
        rank: 2,
        totalStudents: 42,
        subjectGrades: AppData.subjects,
      ),
      ReportCard(
        id: 2,
        term: 'First Terminal Examination 2081',
        academicYear: '2081-2082 (2026)',
        issueDate: '2026-04-10',
        overallGrade: 'A',
        principalRemarks: 'Very good grasp of science and mathematics concepts.',
        gpa: 3.8,
        percentage: 88.2,
        totalMarks: 700,
        obtainedMarks: 617,
        rank: 4,
        totalStudents: 42,
        subjectGrades: AppData.subjects,
      ),
    ];
  }

  // ==========================================================================
  // 4. ATTENDANCE MODULE
  // ==========================================================================
  static Future<List<AttendanceRecord>> getAttendance({int? childId}) async {
    try {
      final url = childId != null
          ? '$baseUrl/attendance/child/$childId'
          : '$baseUrl/attendance';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => AttendanceRecord.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return AppData.attendance;
  }

  static Future<Map<String, dynamic>?> getAttendanceSummary(int childId) async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/attendance/child/$childId/summary'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        return _extractData(response) as Map<String, dynamic>?;
      }
    } catch (_) {}
    return null;
  }

  // ==========================================================================
  // 5. ASSIGNMENTS & HOMEWORK MODULE
  // ==========================================================================
  static Future<List<Assignment>> getAssignments({int? childId}) async {
    try {
      final url = childId != null
          ? '$baseUrl/assignments/child/$childId'
          : '$baseUrl/assignments';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => Assignment.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return AppData.assignments;
  }

  static Future<bool> updateAssignmentStatus(int id, String status) async {
    try {
      final response = await _client.put(
        Uri.parse('$baseUrl/assignments/$id/status'),
        headers: await _getHeaders(),
        body: jsonEncode({'status': status}),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // ==========================================================================
  // 6. EXAMS MODULE
  // ==========================================================================
  static Future<List<ExamItem>> getExams({int? childId}) async {
    try {
      final url = childId != null
          ? '$baseUrl/exams/child/$childId'
          : '$baseUrl/exams/upcoming';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => ExamItem.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return AppData.exams;
  }

  // ==========================================================================
  // 7. FEES & ONLINE PAYMENTS MODULE
  // ==========================================================================
  static Future<List<FeeRecord>> getFees({int? childId}) async {
    try {
      final url = childId != null
          ? '$baseUrl/fees/child/$childId'
          : '$baseUrl/fees';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => FeeRecord.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return AppData.fees;
  }

  static Future<bool> payFee(int feeId, {required String method, required double amount}) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/fees/$feeId/pay'),
        headers: await _getHeaders(),
        body: jsonEncode({
          'payment_method': method,
          'amount': amount,
          'transaction_id': 'TXN-${DateTime.now().millisecondsSinceEpoch}',
        }),
      );
      return response.statusCode == 200 || response.statusCode == 201;
    } catch (_) {
      return true; // Fallback mock success
    }
  }

  // ==========================================================================
  // 8. MESSAGING & CHAT MODULE
  // ==========================================================================
  static Future<List<ConversationItem>> getConversations() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/messages/conversations'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => ConversationItem.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      const ConversationItem(
        id: 1,
        teacherId: 101,
        teacherName: 'Prof. Ram K. Shrestha',
        teacherRole: 'Mathematics Dept. Head',
        teacherAvatar: 'R',
        subject: 'Mathematics',
        lastMessage: 'Aarav has shown remarkable progress in quadratic equations.',
        lastMessageTime: '10:45 AM',
        unreadCount: 1,
        isOnline: true,
      ),
      const ConversationItem(
        id: 2,
        teacherId: 102,
        teacherName: 'Dr. Gita Adhikari',
        teacherRole: 'Class Teacher - Grade 8A',
        teacherAvatar: 'G',
        subject: 'Science & Lab',
        lastMessage: 'Next week we have practical session. Lab coat is required.',
        lastMessageTime: 'Yesterday',
        unreadCount: 0,
        isOnline: false,
      ),
      const ConversationItem(
        id: 3,
        teacherId: 103,
        teacherName: 'Ms. Sunita Rai',
        teacherRole: 'English Faculty',
        teacherAvatar: 'S',
        subject: 'English Literature',
        lastMessage: 'Book review assignment has been submitted successfully.',
        lastMessageTime: '3 days ago',
        unreadCount: 0,
        isOnline: true,
      ),
    ];
  }

  static Future<List<ChatMessage>> getMessages(int conversationId) async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/messages/conversations/$conversationId/messages'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => ChatMessage.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      const ChatMessage(
        sender: 'Prof. Ram K. Shrestha',
        message: 'Namaste! Aarav performed very well in today\'s quiz test.',
        time: '10:30 AM',
        isMe: false,
      ),
      const ChatMessage(
        sender: 'Parent',
        message: 'Thank you Sir! He was practicing every evening.',
        time: '10:38 AM',
        isMe: true,
      ),
      const ChatMessage(
        sender: 'Prof. Ram K. Shrestha',
        message: 'Aarav has shown remarkable progress in quadratic equations. Keep encouraging him for the upcoming Olympiad!',
        time: '10:45 AM',
        isMe: false,
      ),
    ];
  }

  static Future<ChatMessage?> sendMessage(int conversationId, String message) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/messages/conversations/$conversationId/messages'),
        headers: await _getHeaders(),
        body: jsonEncode({'message': message}),
      );
      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = _extractData(response);
        if (data is Map<String, dynamic>) return ChatMessage.fromJson(data);
      }
    } catch (_) {}
    return ChatMessage(sender: 'Parent', message: message, time: 'Just now', isMe: true);
  }

  // ==========================================================================
  // 9. BUS & GPS TRACKING MODULE
  // ==========================================================================
  static Future<BusInfo?> getBusInfo({int? childId}) async {
    try {
      final url = childId != null
          ? '$baseUrl/bus/child/$childId'
          : '$baseUrl/bus/location';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        final data = (body is Map<String, dynamic>)
            ? (body['data'] ?? body)
            : (body is List && body.isNotEmpty ? body[0] : null);
        if (data != null && data is Map<String, dynamic>) return BusInfo.fromJson(data);
      }
    } catch (_) {}
    return AppData.busInfo;
  }

  // ==========================================================================
  // 10. TEACHERS DIRECTORY MODULE
  // ==========================================================================
  static Future<List<TeacherContact>> getTeachers() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/teachers'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => TeacherContact.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      const TeacherContact(
        id: 1,
        name: 'Prof. Ram K. Shrestha',
        subject: 'Mathematics (Higher)',
        designation: 'HOD Mathematics',
        email: 'ram.shrestha@nepalschoolhub.com',
        phone: '+977 9841234590',
        avatar: 'R',
        roomNo: 'Faculty Hall #1',
        consultationHours: '02:00 PM - 03:30 PM (Mon-Fri)',
        rating: 4.9,
      ),
      const TeacherContact(
        id: 2,
        name: 'Dr. Gita Adhikari',
        subject: 'Science & Biology',
        designation: 'Class Teacher 8A',
        email: 'gita.adhikari@nepalschoolhub.com',
        phone: '+977 9841890234',
        avatar: 'G',
        roomNo: 'Science Wing Lab 2',
        consultationHours: '01:30 PM - 02:30 PM (Tue-Thu)',
        rating: 4.8,
      ),
      const TeacherContact(
        id: 3,
        name: 'Ms. Sunita Rai',
        subject: 'English Literature',
        designation: 'Senior English Educator',
        email: 'sunita.rai@nepalschoolhub.com',
        phone: '+977 9851098456',
        avatar: 'S',
        roomNo: 'Staff Room #3',
        consultationHours: '03:00 PM - 04:00 PM (Mon-Wed)',
        rating: 4.9,
      ),
      const TeacherContact(
        id: 4,
        name: 'Er. Sandeep Thapa',
        subject: 'Computer Science & AI',
        designation: 'IT Dept Lead',
        email: 'sandeep.thapa@nepalschoolhub.com',
        phone: '+977 9801239845',
        avatar: 'E',
        roomNo: 'Computer Lab 1',
        consultationHours: '11:00 AM - 12:30 PM (Daily)',
        rating: 5.0,
      ),
    ];
  }

  // ==========================================================================
  // 11. MEETINGS & CONFERENCES (PTA) MODULE
  // ==========================================================================
  static Future<List<MeetingItem>> getMeetings({int? childId}) async {
    try {
      final url = childId != null
          ? '$baseUrl/meetings/child/$childId'
          : '$baseUrl/meetings';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => MeetingItem.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      const MeetingItem(
        id: 1,
        title: 'Mid-Term Academic Progress Consultation',
        teacherName: 'Dr. Gita Adhikari (Class Teacher)',
        teacherAvatar: 'G',
        subject: 'General Academic Review & Term 2 Plan',
        date: '2026-09-29',
        time: '03:15 PM',
        duration: '25 mins',
        status: 'confirmed',
        meetingType: 'Virtual Video Meeting',
        meetLink: 'https://meet.google.com/nsh-parent-call',
        notes: 'Discussion on Aarav\'s preparation for state mathematics Olympiad.',
      ),
      const MeetingItem(
        id: 2,
        title: 'Science Project Collaboration Review',
        teacherName: 'Er. Sandeep Thapa',
        teacherAvatar: 'E',
        subject: 'AI & Robotics Exhibition',
        date: '2026-10-04',
        time: '04:00 PM',
        duration: '20 mins',
        status: 'pending',
        meetingType: 'In-person (Staff Room 3)',
        notes: 'Review components required for the smart solar tracker model.',
      ),
    ];
  }

  static Future<bool> requestMeeting(Map<String, dynamic> data) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/meetings'),
        headers: await _getHeaders(),
        body: jsonEncode(data),
      );
      return response.statusCode == 200 || response.statusCode == 201;
    } catch (_) {
      return true;
    }
  }

  // ==========================================================================
  // 12. EVENTS & SCHOOL CALENDAR MODULE
  // ==========================================================================
  static Future<List<EventItem>> getEvents() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/events'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => EventItem.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      const EventItem(
        id: 1,
        title: 'Annual Inter-School Science & Tech Expo 2081',
        description: 'Over 40 STEM and robotics projects made by secondary students.',
        category: 'Exhibition & STEM',
        startDate: '2026-10-02',
        time: '10:00 AM - 04:30 PM',
        location: 'Main School Auditorium & Quadrangle',
        colorHex: 0xFF6C3CE9,
        rsvpStatus: 'going',
      ),
      const EventItem(
        id: 2,
        title: 'Dashain & Tihar Cultural Grand Program',
        description: 'Traditional cultural dances, music, and student art showcase.',
        category: 'Cultural Event',
        startDate: '2026-10-14',
        time: '11:00 AM - 03:00 PM',
        location: 'School Open Amphitheatre',
        colorHex: 0xFFFF9F1A,
        rsvpStatus: 'going',
      ),
      const EventItem(
        id: 3,
        title: 'Dashain Vacation Break',
        description: 'School remains closed for official Dashain holidays.',
        category: 'Holiday',
        startDate: '2026-10-18',
        endDate: '2026-10-31',
        time: 'All Day',
        location: 'Campus Closed',
        isHoliday: true,
        colorHex: 0xFF05C46B,
      ),
    ];
  }

  // ==========================================================================
  // 13. HEALTH & MEDICAL MODULE
  // ==========================================================================
  static Future<HealthProfile> getChildHealth(int? childId) async {
    try {
      final url = childId != null ? '$baseUrl/health/child/$childId' : '$baseUrl/health/vaccinations';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is Map<String, dynamic>) {
          return HealthProfile(
            bloodGroup: data['blood_group'] ?? 'B+ Positive',
            height: data['height'] ?? '148 cm',
            weight: data['weight'] ?? '42 kg',
            emergencyContact: data['emergency_contact'] ?? '+977 9841000001 (Father)',
            doctorContact: data['doctor_contact'] ?? '+977 1-4433221 (Dr. Anupa Thapa)',
          );
        }
      }
    } catch (_) {}
    return const HealthProfile(
      bloodGroup: 'B+ Positive',
      height: '148 cm',
      weight: '42 kg',
      bmi: '19.2 (Normal)',
      emergencyContact: '+977 9841000001 (Father)',
      doctorContact: '+977 1-4433221 (Family Clinic)',
      allergies: ['Peanuts (Mild sensitivity)', 'Dust mite'],
      medications: ['Asthalin Inhaler as needed'],
      chronicConditions: ['Mild Exercise-induced Bronchospasm'],
      vaccinations: [
        VaccinationItem(vaccineName: 'Tetanus Toxoid (TT Booster)', givenDate: '2026-02-10', dueDate: 'Up to date', status: 'Completed'),
        VaccinationItem(vaccineName: 'MMR 2nd Dose', givenDate: '2024-05-18', dueDate: 'Up to date', status: 'Completed'),
        VaccinationItem(vaccineName: 'Hepatitis B Complete', givenDate: '2023-11-04', dueDate: 'Up to date', status: 'Completed'),
        VaccinationItem(vaccineName: 'Annual Influenza Booster', givenDate: 'Pending', dueDate: '2026-11-15', status: 'Upcoming'),
      ],
    );
  }

  // ==========================================================================
  // 14. BEHAVIOR & DISCIPLINE MODULE
  // ==========================================================================
  static Future<List<BehaviorEntry>> getBehaviorRecords(int? childId) async {
    try {
      final url = childId != null ? '$baseUrl/behavior/child/$childId' : '$baseUrl/behavior/commendations';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => BehaviorEntry.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      const BehaviorEntry(
        id: 1,
        title: 'Best Team Leader Award in Robotics Workshop',
        description: 'Mentored junior peers and exhibited exemplary cooperation and humility.',
        category: 'Leadership & STEM',
        type: 'merit',
        date: '2026-09-18',
        teacherName: 'Er. Sandeep Thapa',
        points: 20,
        colorHex: 0xFF05C46B,
      ),
      const BehaviorEntry(
        id: 2,
        title: '100% On-Time Homework Submission',
        description: 'Delivered all 12 consecutive assignments with neat handwriting and diagrams.',
        category: 'Academics & Discipline',
        type: 'merit',
        date: '2026-09-08',
        teacherName: 'Dr. Gita Adhikari',
        points: 15,
        colorHex: 0xFF05C46B,
      ),
      const BehaviorEntry(
        id: 3,
        title: 'Spoken English & Debate Star',
        description: 'Secured 1st position in inter-house speech contest on Green Nepal.',
        category: 'Co-curricular Excellence',
        type: 'merit',
        date: '2026-08-22',
        teacherName: 'Ms. Sunita Rai',
        points: 25,
        colorHex: 0xFF6C3CE9,
      ),
    ];
  }

  // ==========================================================================
  // 15. LEAVE REQUESTS MODULE
  // ==========================================================================
  static Future<List<LeaveRequestItem>> getLeaveRequests({int? childId}) async {
    try {
      final url = childId != null ? '$baseUrl/leave-requests/child/$childId' : '$baseUrl/leave-requests';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => LeaveRequestItem.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      const LeaveRequestItem(
        id: 1,
        childName: 'Aarav Sharma',
        leaveType: 'Medical / Viral Fever Recovery',
        startDate: '2026-09-22',
        endDate: '2026-09-23',
        reason: 'Rest advised by physician due to high seasonal viral fever.',
        status: 'approved',
        appliedDate: '2026-09-21',
        approvedBy: 'Dr. Gita Adhikari (Class Teacher)',
        remarks: 'Leave sanctioned. Catch up on lab notes.',
        totalDays: 2,
      ),
      const LeaveRequestItem(
        id: 2,
        childName: 'Aarav Sharma',
        leaveType: 'Family Function / Urgent Travel',
        startDate: '2026-08-11',
        endDate: '2026-08-12',
        reason: 'Attending grandfather ceremony in Pokhara.',
        status: 'approved',
        appliedDate: '2026-08-09',
        approvedBy: 'Principal Office',
        remarks: 'Approved.',
        totalDays: 2,
      ),
    ];
  }

  static Future<bool> submitLeaveRequest(Map<String, dynamic> data) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/leave-requests'),
        headers: await _getHeaders(),
        body: jsonEncode(data),
      );
      return response.statusCode == 200 || response.statusCode == 201;
    } catch (_) {
      return true;
    }
  }

  // ==========================================================================
  // 16. NOTIFICATIONS MODULE
  // ==========================================================================
  static Future<List<AppNotification>> getNotifications() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/notifications'),
        headers: await _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => AppNotification.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return AppData.notifications;
  }

  static Future<bool> markAllNotificationsRead() async {
    try {
      final response = await _client.put(
        Uri.parse('$baseUrl/notifications/mark-all-read'),
        headers: await _getHeaders(),
      );
      return response.statusCode == 200;
    } catch (_) {
      return true;
    }
  }

  // ==========================================================================
  // 17. DIGITAL DOCUMENTS MODULE
  // ==========================================================================
  static Future<List<DocumentItem>> getDocuments({int? childId}) async {
    try {
      final url = childId != null ? '$baseUrl/documents/child/$childId' : '$baseUrl/documents';
      final response = await _client.get(Uri.parse(url), headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = _extractData(response);
        if (data is List && data.isNotEmpty) {
          return data.map((item) => DocumentItem.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
    } catch (_) {}
    return [
      const DocumentItem(
        id: 1,
        title: 'Birth Certificate (Verified Copy).pdf',
        type: 'Official Document',
        fileSize: '1.4 MB',
        date: '2026-04-12',
        downloadUrl: 'https://nepalschoolhub.techprocod.com.np/docs/birth_cert.pdf',
      ),
      const DocumentItem(
        id: 2,
        title: 'Annual Health & Immunization Record.pdf',
        type: 'Medical Record',
        fileSize: '840 KB',
        date: '2026-05-18',
        downloadUrl: 'https://nepalschoolhub.techprocod.com.np/docs/health.pdf',
      ),
      const DocumentItem(
        id: 3,
        title: 'Previous School Transfer Certificate (TC).pdf',
        type: 'Admission Record',
        fileSize: '2.1 MB',
        date: '2026-04-02',
        downloadUrl: 'https://nepalschoolhub.techprocod.com.np/docs/tc.pdf',
      ),
    ];
  }
}
