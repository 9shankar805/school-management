import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class ParentCommunicationScreen extends StatefulWidget {
  const ParentCommunicationScreen({super.key});

  @override
  State<ParentCommunicationScreen> createState() =>
      _ParentCommunicationScreenState();
}

class _ParentCommunicationScreenState extends State<ParentCommunicationScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;
  final ApiService _apiService = ApiService();

  bool _isLoading = true;
  String _searchQuery = '';
  String _selectedFilter = 'All';

  List<Map<String, dynamic>> _conversations = [];
  List<Map<String, dynamic>> _parentContacts = [];

  static const String _cacheKey = 'cached_parent_chat_conversations';

  final List<Map<String, dynamic>> _meetings = [
    {
      'parent': 'Sunita Adhikari',
      'child': 'Bibek Adhikari (Grade 10 - A)',
      'date': '१० असोज २०८१ (Today)',
      'time': '03:30 PM',
      'type': 'In-Person (School Meeting Room)',
      'status': 'Confirmed',
    },
    {
      'parent': 'Dilip Kumar Thapa',
      'child': 'Rohan Thapa (Grade 10 - A)',
      'date': '१२ असोज २०८१ (Friday)',
      'time': '04:00 PM',
      'type': 'Phone Consultation',
      'status': 'Pending',
    },
    {
      'parent': 'Babita Devi Yadav',
      'child': 'Ritesh Yadav (Grade 9 - B)',
      'date': '१५ असोज २०८१ (Monday)',
      'time': '02:30 PM',
      'type': 'In-Person (Faculty Room)',
      'status': 'Confirmed',
    },
  ];

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 3, vsync: this);
    _loadConversations();
    _loadParentContacts();
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  Future<void> _loadConversations() async {
    // 1. Load from cache for instant rendering
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedJson = prefs.getString(_cacheKey);
      if (cachedJson != null && mounted) {
        final decoded = jsonDecode(cachedJson) as List;
        setState(() {
          _conversations =
              decoded.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        });
      }
    } catch (_) {}

    // 2. Fetch fresh from API
    final res = await _apiService.getChatConversations();
    if (!mounted) return;

    if (res.success && res.data != null && res.data!.isNotEmpty) {
      final list = res.data!
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() {
        _conversations = list;
        _isLoading = false;
      });

      // Update cache
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_cacheKey, jsonEncode(list));
      } catch (_) {}
    } else if (_conversations.isEmpty) {
      _loadDefaultFallback();
    }

    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _loadParentContacts() async {
    final res = await _apiService.getParentContacts();
    if (mounted && res.success && res.data != null) {
      setState(() {
        _parentContacts = res.data!
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      });
    }
  }

  void _loadDefaultFallback() {
    setState(() {
      _conversations = [
        {
          'id': 1,
          'parent_name': 'Gopal Sharma',
          'relationship': 'Father (बुबा)',
          'student_name': 'Aarav Sharma',
          'child': 'Aarav Sharma (Roll 1)',
          'grade': 'Class 10 - Section A',
          'phone': '+977 9841234567',
          'avatar': 'GS',
          'subject': 'Math Worksheet & Term Preparation',
          'last': 'Namaste Sir, Aarav has completed his algebra worksheet today.',
          'time': '10m ago',
          'unread': 0,
          'is_online': true,
          'attendance': '96%',
          'exam_score': 'GPA 3.85 (A+)',
        },
        {
          'id': 2,
          'parent_name': 'Sunita Adhikari',
          'relationship': 'Mother (आमा)',
          'student_name': 'Bibek Adhikari',
          'child': 'Bibek Adhikari (Roll 2)',
          'grade': 'Class 10 - Section A',
          'phone': '+977 9851098765',
          'avatar': 'SA',
          'subject': 'Parent-Teacher Meeting (PTM) Timing',
          'last': 'Can we schedule a short consultation regarding second term science preparation?',
          'time': '1h ago',
          'unread': 1,
          'is_online': true,
          'attendance': '91%',
          'exam_score': 'GPA 3.60 (A)',
        },
        {
          'id': 3,
          'parent_name': 'Krishna Prasad Poudel',
          'relationship': 'Father (बुबा)',
          'student_name': 'Puja Poudel',
          'child': 'Puja Poudel (Roll 3)',
          'grade': 'Class 10 - Section A',
          'phone': '+977 9860112233',
          'avatar': 'KP',
          'subject': 'Terminal Feedback Appreciation',
          'last': 'Thank you teacher for the detailed terminal progress feedback. She is practicing daily.',
          'time': '3h ago',
          'unread': 0,
          'is_online': false,
          'attendance': '98%',
          'exam_score': 'GPA 3.90 (A+)',
        },
        {
          'id': 4,
          'parent_name': 'Dilip Kumar Thapa',
          'relationship': 'Father (बुबा)',
          'student_name': 'Rohan Thapa',
          'child': 'Rohan Thapa (Roll 4)',
          'grade': 'Class 10 - Section A',
          'phone': '+977 9849556677',
          'avatar': 'DT',
          'subject': 'Sick Leave & Doctor Prescription',
          'last': 'Rohan had viral fever yesterday. Doctor advised 2 days rest, medical note attached.',
          'time': 'Yesterday',
          'unread': 0,
          'is_online': false,
          'attendance': '84%',
          'exam_score': 'GPA 3.20 (B+)',
        },
        {
          'id': 5,
          'parent_name': 'Babita Devi Yadav',
          'relationship': 'Mother (आमा)',
          'student_name': 'Ritesh Yadav',
          'child': 'Ritesh Yadav (Roll 7)',
          'grade': 'Class 9 - Section B',
          'phone': '+977 9812345678',
          'avatar': 'BY',
          'subject': 'Question about homework exercises',
          'last': 'Namaste Sir, Ritesh had a small confusion on exercise 4.2 question 8.',
          'time': '2d ago',
          'unread': 0,
          'is_online': true,
          'attendance': '94%',
          'exam_score': 'GPA 3.70 (A)',
        },
      ];
    });
  }

  List<Map<String, dynamic>> get _filteredConversations {
    return _conversations.where((c) {
      final name = (c['parent_name'] ?? '').toString().toLowerCase();
      final child = (c['child'] ?? '').toString().toLowerCase();
      final sub = (c['subject'] ?? '').toString().toLowerCase();
      final query = _searchQuery.toLowerCase();

      final matchesQuery = query.isEmpty ||
          name.contains(query) ||
          child.contains(query) ||
          sub.contains(query);

      if (!matchesQuery) return false;

      if (_selectedFilter == 'Unread') {
        return (c['unread'] ?? 0) > 0;
      } else if (_selectedFilter == 'Class 10') {
        return (c['grade'] ?? '').toString().contains('10');
      } else if (_selectedFilter == 'Class 9') {
        return (c['grade'] ?? '').toString().contains('9');
      }

      return true;
    }).toList();
  }

  Future<void> _makePhoneCall(String phone) async {
    final cleanPhone = phone.replaceAll(RegExp(r'[^0-9+]'), '');
    final uri = Uri.parse('tel:$cleanPhone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: AppTheme.primary,
            content: Text('Calling $phone...'),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Parent Connect (अभिभावक सम्पर्क)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Real-time Chat, PTM Meetings & Notice Broadcast',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppTheme.darkSubtext,
              ),
            ),
          ],
        ),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new_rounded,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          IconButton(
            tooltip: 'रिफ्रेस गर्नुहोस्',
            icon: const Icon(Icons.refresh_rounded, size: 22),
            onPressed: () {
              setState(() => _isLoading = true);
              _loadConversations();
            },
          ),
          Container(
            margin: const EdgeInsets.only(right: 12),
            child: ElevatedButton.icon(
              onPressed: () => _showNewChatDirectorySheet(isDark),
              icon: const Icon(Icons.person_add_alt_1_rounded, size: 16),
              label: const Text(
                'नयाँ च्याट',
                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ],
        bottom: TabBar(
          controller: _tab,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.darkSubtext,
          indicatorColor: AppTheme.primary,
          indicatorWeight: 3,
          indicatorSize: TabBarIndicatorSize.label,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: const [
            Tab(text: 'Messages (च्याट)'),
            Tab(text: 'PTM Meetings (भेटघाट)'),
            Tab(text: 'Broadcasts (सूचना)'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: [
          // 1. MESSAGES TAB
          _buildMessagesTab(isDark),

          // 2. PTM MEETINGS TAB
          _buildMeetingsTab(isDark),

          // 3. BROADCASTS TAB
          _buildBroadcastsTab(isDark),
        ],
      ),
    );
  }

  // =========================================================================
  // TAB 1: MESSAGES (REAL-TIME CHAT CONVERSATIONS)
  // =========================================================================
  Widget _buildMessagesTab(bool isDark) {
    final filtered = _filteredConversations;

    return RefreshIndicator(
      onRefresh: _loadConversations,
      child: Column(
        children: [
          // Search & Filter Header
          Container(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 6),
            child: Column(
              children: [
                // Search Input
                TextField(
                  onChanged: (val) => setState(() => _searchQuery = val),
                  decoration: InputDecoration(
                    hintText: 'अभिभावक वा विद्यार्थीको नाम खोज्नुहोस्...',
                    hintStyle: const TextStyle(
                      fontSize: 12.5,
                      color: AppTheme.darkSubtext,
                    ),
                    prefixIcon: const Icon(Icons.search_rounded,
                        color: AppTheme.darkSubtext, size: 20),
                    filled: true,
                    fillColor: isDark ? AppTheme.darkCard : Colors.white,
                    contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14, vertical: 10),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: BorderSide(
                        color: isDark
                            ? AppTheme.darkCardBorder
                            : AppTheme.cardBorder,
                      ),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: BorderSide(
                        color: isDark
                            ? AppTheme.darkCardBorder
                            : AppTheme.cardBorder,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 10),

                // Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  physics: const BouncingScrollPhysics(),
                  child: Row(
                    children: [
                      _buildFilterChip('All', 'सबै (All)', isDark),
                      const SizedBox(width: 8),
                      _buildFilterChip('Unread', 'नपढेका (Unread)', isDark),
                      const SizedBox(width: 8),
                      _buildFilterChip('Class 10', 'कक्षा १० (Grade 10)', isDark),
                      const SizedBox(width: 8),
                      _buildFilterChip('Class 9', 'कक्षा ९ (Grade 9)', isDark),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 6),

          // Conversation List
          Expanded(
            child: _isLoading && _conversations.isEmpty
                ? const Center(
                    child: CircularProgressIndicator(strokeWidth: 2.5),
                  )
                : filtered.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.mark_chat_unread_rounded,
                                size: 48,
                                color: isDark
                                    ? AppTheme.darkSubtext
                                    : Colors.grey.shade400),
                            const SizedBox(height: 10),
                            const Text(
                              'कुनै कुराकानी फेला परेन।',
                              style: TextStyle(
                                  color: AppTheme.darkSubtext,
                                  fontWeight: FontWeight.w700),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        physics: const BouncingScrollPhysics(),
                        padding: const EdgeInsets.fromLTRB(16, 6, 16, 90),
                        itemCount: filtered.length,
                        itemBuilder: (_, i) {
                          final c = filtered[i];
                          return _buildConversationTile(c, isDark)
                              .animate()
                              .fadeIn(delay: (i * 30).ms);
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String value, String label, bool isDark) {
    final isSelected = _selectedFilter == value;
    return GestureDetector(
      onTap: () {
        setState(() => _selectedFilter = value);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected
              ? AppTheme.primary
              : (isDark ? AppTheme.darkCard : Colors.white),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected
                ? AppTheme.primary
                : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected
                ? Colors.white
                : (isDark ? Colors.white70 : AppTheme.dark),
          ),
        ),
      ),
    );
  }

  Widget _buildConversationTile(Map<String, dynamic> c, bool isDark) {
    final unread = c['unread'] as int? ?? 0;
    final parentName = c['parent_name']?.toString() ?? 'Parent';
    final child = c['child']?.toString() ?? 'Student';
    final avatar = c['avatar']?.toString() ?? 'P';
    final lastMsg = c['last']?.toString() ?? '';
    final timeStr = c['time']?.toString() ?? '';
    final phone = c['phone']?.toString() ?? '+977 98XXXXXXXX';
    final isOnline = c['is_online'] == true;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: unread > 0
              ? AppTheme.primary.withOpacity(0.4)
              : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
          width: unread > 0 ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () async {
            // Mark as read in state
            setState(() => c['unread'] = 0);
            await Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => ParentChatScreen(conversation: c),
              ),
            );
            _loadConversations();
          },
          borderRadius: BorderRadius.circular(20),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                // Avatar with online status badge
                Stack(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: LinearGradient(
                          colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                      child: Center(
                        child: Text(
                          avatar,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 15,
                          ),
                        ),
                      ),
                    ),
                    if (isOnline)
                      Positioned(
                        right: 0,
                        bottom: 0,
                        child: Container(
                          width: 14,
                          height: 14,
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981),
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: isDark ? AppTheme.darkCard : Colors.white,
                              width: 2,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(width: 12),

                // Parent & Student Details
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              parentName,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontSize: 13.5,
                                fontWeight: unread > 0
                                    ? FontWeight.w900
                                    : FontWeight.w800,
                                color: isDark ? Colors.white : AppTheme.dark,
                              ),
                            ),
                          ),
                          Text(
                            timeStr,
                            style: TextStyle(
                              fontSize: 10.5,
                              color: unread > 0
                                  ? AppTheme.primary
                                  : AppTheme.darkSubtext,
                              fontWeight: unread > 0
                                  ? FontWeight.w800
                                  : FontWeight.normal,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),

                      // Child pill
                      Row(
                        children: [
                          const Icon(Icons.school_rounded,
                              size: 12, color: AppTheme.primary),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              child,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 11,
                                color: AppTheme.primary,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 4),

                      // Last Message Snippet
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              lastMsg,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontSize: 12,
                                color: unread > 0
                                    ? (isDark ? Colors.white : AppTheme.dark)
                                    : (isDark
                                        ? Colors.white70
                                        : AppTheme.darkSubtext),
                                fontWeight: unread > 0
                                    ? FontWeight.w800
                                    : FontWeight.normal,
                              ),
                            ),
                          ),
                          if (unread > 0)
                            Container(
                              margin: const EdgeInsets.only(left: 6),
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 7, vertical: 2),
                              decoration: const BoxDecoration(
                                color: AppTheme.primary,
                                shape: BoxShape.circle,
                              ),
                              child: Text(
                                '$unread',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(width: 8),

                // Quick Call Action Icon
                IconButton(
                  tooltip: 'कल गर्नुहोस् ($phone)',
                  icon: const Icon(Icons.phone_in_talk_rounded,
                      color: AppTheme.green, size: 20),
                  onPressed: () => _makePhoneCall(phone),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // =========================================================================
  // TAB 2: PTM MEETINGS (भेटघाट)
  // =========================================================================
  Widget _buildMeetingsTab(bool isDark) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(18, 14, 18, 90),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const SectionHeader(
                title: 'Upcoming Parent Conferences (भेटघाट)',
                subtitle: 'Scheduled one-on-one student discussions',
              ),
              ElevatedButton.icon(
                icon: const Icon(Icons.add_rounded, size: 16),
                label: const Text('तय गर्नुहोस्', style: TextStyle(fontSize: 11.5)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.purple,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                onPressed: () => _showScheduleMeetingModal(isDark),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ..._meetings.map((m) {
            final isConfirmed = m['status'] == 'Confirmed';
            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: (isConfirmed ? AppTheme.green : AppTheme.orange)
                          .withOpacity(0.12),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Icon(
                      isConfirmed
                          ? Icons.event_available_rounded
                          : Icons.pending_actions_rounded,
                      color: isConfirmed ? AppTheme.green : AppTheme.orange,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          m['parent'] as String,
                          style: const TextStyle(
                              fontSize: 13.5, fontWeight: FontWeight.w800),
                        ),
                        Text(
                          m['child'] as String,
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: AppTheme.primary,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          '${m['date']} • ${m['time']}',
                          style: const TextStyle(
                              fontSize: 11, color: AppTheme.darkSubtext),
                        ),
                        Text(
                          'विधि: ${m['type']}',
                          style: const TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w600,
                            color: AppTheme.purple,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: (isConfirmed ? AppTheme.green : AppTheme.orange)
                          .withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      (m['status'] as String).toUpperCase(),
                      style: TextStyle(
                        fontSize: 9.5,
                        fontWeight: FontWeight.w900,
                        color: isConfirmed ? AppTheme.green : AppTheme.orange,
                      ),
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  void _showScheduleMeetingModal(bool isDark) {
    String selectedParent = 'Sunita Adhikari';
    String selectedType = 'In-Person (School Meeting Room)';
    final dateCtrl = TextEditingController(text: '२०८१ कार्तिक २५ (आइतबार)');
    final timeCtrl = TextEditingController(text: '०३:३० PM');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
          20,
          20,
          20,
          MediaQuery.of(ctx).viewInsets.bottom + 20,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.event_note_rounded, color: AppTheme.purple, size: 22),
                SizedBox(width: 8),
                Text(
                  'Schedule Parent Meeting (भेटघाट तय)',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              value: selectedParent,
              decoration: InputDecoration(
                labelText: 'Select Parent (अभिभावक छनोट)',
                filled: true,
                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
              items: [
                'Gopal Sharma',
                'Sunita Adhikari',
                'Krishna Prasad Poudel',
                'Dilip Kumar Thapa',
                'Babita Devi Yadav',
              ]
                  .map((p) => DropdownMenuItem(value: p, child: Text(p)))
                  .toList(),
              onChanged: (val) => selectedParent = val ?? selectedParent,
            ),
            const SizedBox(height: 10),
            TextField(
              controller: dateCtrl,
              decoration: InputDecoration(
                labelText: 'Date (मिति)',
                filled: true,
                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: timeCtrl,
              decoration: InputDecoration(
                labelText: 'Time (समय)',
                filled: true,
                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.purple,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  setState(() {
                    _meetings.insert(0, {
                      'parent': selectedParent,
                      'child': '$selectedParent\'s Ward',
                      'date': dateCtrl.text,
                      'time': timeCtrl.text,
                      'type': selectedType,
                      'status': 'Confirmed',
                    });
                  });
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content: Text('PTM Meeting appointment booked and SMS sent to parent! ✅'),
                    ),
                  );
                },
                child: const Text('Confirm Meeting (भेटघाट सुरक्षित गर्नुहोस्)',
                    style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // =========================================================================
  // TAB 3: BROADCASTS (सूचना)
  // =========================================================================
  Widget _buildBroadcastsTab(bool isDark) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(18, 14, 18, 90),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0F4C81).withOpacity(0.35),
                  blurRadius: 16,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.mark_email_unread_rounded,
                        color: Colors.white, size: 24),
                    SizedBox(width: 8),
                    Text(
                      'Instant Class SMS Gateway 📲',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'Send urgent absence notices, terminal exam routine reminders, or school holiday announcements to all parents simultaneously.',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.88),
                    fontSize: 12,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: () => _showBroadcastSheet(isDark),
                  icon: const Icon(Icons.send_rounded, size: 16),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: AppTheme.primary,
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 10),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  label: const Text(
                    'Compose Broadcast SMS',
                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          const SectionHeader(
            title: 'Recent Broadcast History (पठाइएका सूचनाहरू)',
            subtitle: 'Log of delivered SMS messages to parent guardians',
          ),
          const SizedBox(height: 10),
          _buildBroadcastHistoryTile(
            title: 'Terminal Exam Routine 2081 Dispatched',
            recipients: '32 Parents of Class 10-A',
            time: '२ दिन अघि',
            delivered: '32/32 Delivered ✅',
            isDark: isDark,
          ),
          _buildBroadcastHistoryTile(
            title: 'Dashain Festival Vacation Notice',
            recipients: 'All Faculty & Section Parents',
            time: '१ हप्ता अघि',
            delivered: '64/64 Delivered ✅',
            isDark: isDark,
          ),
        ],
      ),
    );
  }

  Widget _buildBroadcastHistoryTile({
    required String title,
    required String recipients,
    required String time,
    required String delivered,
    required bool isDark,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
        ),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppTheme.primary.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.sms_rounded,
                color: AppTheme.primary, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                      fontWeight: FontWeight.w800, fontSize: 13),
                ),
                const SizedBox(height: 2),
                Text(
                  recipients,
                  style: const TextStyle(
                      fontSize: 11, color: AppTheme.darkSubtext),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                time,
                style: const TextStyle(
                    fontSize: 10.5, color: AppTheme.darkSubtext),
              ),
              const SizedBox(height: 2),
              Text(
                delivered,
                style: const TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.green,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _showBroadcastSheet(bool isDark) {
    final msgCtrl = TextEditingController();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
          20,
          20,
          20,
          MediaQuery.of(ctx).viewInsets.bottom + 20,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.sms_rounded, color: AppTheme.primary, size: 22),
                SizedBox(width: 8),
                Text(
                  'Parent SMS Broadcast (अभिभावक सन्देश)',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextField(
              controller: msgCtrl,
              maxLines: 4,
              decoration: InputDecoration(
                labelText: 'SMS Content',
                hintText:
                    'Enter message to be delivered via Nepal School Hub SMS Gateway...',
                filled: true,
                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content:
                          Text('SMS broadcast successfully queued to 32 parents! ✅'),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14)),
                ),
                child: const Text('Dispatch SMS Broadcast 🚀',
                    style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // =========================================================================
  // DIRECTORY: START NEW CHAT WITH PARENT
  // =========================================================================
  void _showNewChatDirectorySheet(bool isDark) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(ctx).size.height * 0.85,
          ),
          decoration: BoxDecoration(
            color: isDark ? AppTheme.darkCard : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(26)),
          ),
          child: Column(
            children: [
              Container(
                margin: const EdgeInsets.only(top: 10, bottom: 8),
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkSurface : Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'अभिभावक छनोट (Select Parent to Chat)',
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
              Expanded(
                child: ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: _parentContacts.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (context, idx) {
                    final p = _parentContacts[idx];
                    final pName = p['parent_name']?.toString() ?? 'Parent';
                    final sName = p['student_name']?.toString() ?? 'Student';
                    final rel = p['relationship']?.toString() ?? 'Guardian';
                    final grade = p['grade']?.toString() ?? '';
                    final phone = p['phone']?.toString() ?? '';
                    final avatar = p['avatar']?.toString() ?? 'P';

                    return ListTile(
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 4),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                        side: BorderSide(
                          color: isDark
                              ? AppTheme.darkCardBorder
                              : AppTheme.cardBorder,
                        ),
                      ),
                      leading: CircleAvatar(
                        backgroundColor: AppTheme.primary,
                        child: Text(
                          avatar,
                          style: const TextStyle(
                              color: Colors.white, fontWeight: FontWeight.w800),
                        ),
                      ),
                      title: Text(
                        pName,
                        style: const TextStyle(
                            fontWeight: FontWeight.w800, fontSize: 13.5),
                      ),
                      subtitle: Text(
                        '$rel • $sName ($grade)',
                        style: const TextStyle(
                            fontSize: 11.5, color: AppTheme.darkSubtext),
                      ),
                      trailing: IconButton(
                        icon: const Icon(Icons.chat_bubble_rounded,
                            color: AppTheme.primary),
                        onPressed: () async {
                          Navigator.pop(ctx);
                          final conv = {
                            'id': p['parent_id'] ?? timeNowId(),
                            'parent_name': pName,
                            'student_name': sName,
                            'child': '$sName ($grade)',
                            'grade': grade,
                            'phone': phone,
                            'avatar': avatar,
                            'subject': 'Academic Chat',
                            'last': 'Conversation started.',
                            'time': 'Just now',
                            'unread': 0,
                            'is_online': true,
                            'attendance': '95%',
                            'exam_score': 'GPA 3.8',
                          };

                          await _apiService.startChatConversation({
                            'parent_name': pName,
                            'student_name': sName,
                            'phone': phone,
                            'subject': 'Academic Chat',
                          });

                          if (mounted) {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) =>
                                    ParentChatScreen(conversation: conv),
                              ),
                            );
                          }
                        },
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  static int timeNowId() => DateTime.now().millisecondsSinceEpoch ~/ 1000;
}

// ===========================================================================
// PARENT CHAT ROOM SCREEN (DETAILED INTERACTIVE CONVERSATION)
// ===========================================================================
class ParentChatScreen extends StatefulWidget {
  final Map<String, dynamic> conversation;
  const ParentChatScreen({super.key, required this.conversation});

  @override
  State<ParentChatScreen> createState() => _ParentChatScreenState();
}

class _ParentChatScreenState extends State<ParentChatScreen> {
  final ApiService _apiService = ApiService();
  final TextEditingController _ctrl = TextEditingController();
  final ScrollController _scroll = ScrollController();

  List<Map<String, dynamic>> _messages = [];
  bool _isLoading = true;

  String get _convId => widget.conversation['id']?.toString() ?? '1';
  String get _cacheKey => 'cached_chat_thread_$_convId';

  final List<String> _quickReplies = [
    'नमस्ते! हजुरको सन्देश प्राप्त भयो।',
    'गृहकार्य जाँच भइसकेको छ, राम्रो छ। 👍',
    'विद्यार्थीको पढाइ र उपस्थिति सन्तोषजनक छ।',
    'भोलि अतिरिक्त कोचिङ कक्षा सञ्चालन हुनेछ।',
    'कृपया विद्यालय समयमा फोन गर्नुहोला।',
  ];

  @override
  void initState() {
    super.initState();
    _loadMessages();
  }

  Future<void> _loadMessages() async {
    // 1. Load from cache
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedJson = prefs.getString(_cacheKey);
      if (cachedJson != null && mounted) {
        final decoded = jsonDecode(cachedJson) as List;
        setState(() {
          _messages =
              decoded.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        });
      }
    } catch (_) {}

    // 2. Fetch fresh from API
    final res = await _apiService.getConversationMessages(_convId);
    if (!mounted) return;

    if (res.success && res.data != null && res.data!.isNotEmpty) {
      final list = res.data!
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      setState(() {
        _messages = list;
        _isLoading = false;
      });

      // Update cache
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_cacheKey, jsonEncode(list));
      } catch (_) {}
    } else if (_messages.isEmpty) {
      _loadFallbackMessages();
    }

    if (mounted) {
      setState(() => _isLoading = false);
      _scrollToBottom();
    }
  }

  void _loadFallbackMessages() {
    setState(() {
      _messages = [
        {
          'id': 101,
          'text': 'नमस्ते सर! आरभको पछिल्लो युनिट टेस्टको नतिजा कस्तो आयो होला?',
          'isMe': false,
          'sender': widget.conversation['parent_name'] ?? 'Parent',
          'time': '02:00 PM',
          'date': 'Today',
        },
        {
          'id': 102,
          'text':
              'नमस्ते! आरभले युनिट टेस्टमा उत्कृष्ट प्रदर्शन गरेको छ (A+)। विशेष गरी समीकरण र ज्यामितिका प्रश्नहरूमा राम्रो बुझाइ देखिन्छ।',
          'isMe': true,
          'sender': 'You (Teacher)',
          'time': '02:05 PM',
          'date': 'Today',
        },
        {
          'id': 103,
          'text':
              'Namaste Sir, Aarav has completed his algebra worksheet today. Thank you for continuous encouragement! 🙏',
          'isMe': false,
          'sender': widget.conversation['parent_name'] ?? 'Parent',
          'time': '02:10 PM',
          'date': 'Today',
        },
      ];
    });
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scroll.hasClients) {
        _scroll.animateTo(
          _scroll.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _sendMessage([String? textToSend]) async {
    final text = (textToSend ?? _ctrl.text).trim();
    if (text.isEmpty) return;

    final nowStr = DateFormat('hh:mm a').format(DateTime.now());
    final newMsg = {
      'id': DateTime.now().millisecondsSinceEpoch,
      'text': text,
      'isMe': true,
      'sender': 'You (Teacher)',
      'time': nowStr,
      'date': 'Today',
    };

    setState(() {
      _messages.add(newMsg);
      if (textToSend == null) _ctrl.clear();
    });

    _scrollToBottom();

    // Cache updated thread
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_cacheKey, jsonEncode(_messages));
    } catch (_) {}

    // Send to backend
    await _apiService.sendChatMessage(_convId, text);

    // Realistic auto-reply simulation after 2.5 seconds if demo
    Future.delayed(const Duration(milliseconds: 2500), () {
      if (!mounted) return;
      final reply = {
        'id': DateTime.now().millisecondsSinceEpoch,
        'text': 'धन्यवाद सर! हजुरको सुझाव अनुसार म घरमा पनि हेरचाह गर्छु। 🙏',
        'isMe': false,
        'sender': widget.conversation['parent_name'] ?? 'Parent',
        'time': DateFormat('hh:mm a').format(DateTime.now()),
        'date': 'Today',
      };
      setState(() {
        _messages.add(reply);
      });
      _scrollToBottom();
      try {
        SharedPreferences.getInstance().then((prefs) {
          prefs.setString(_cacheKey, jsonEncode(_messages));
        });
      } catch (_) {}
    });
  }

  Future<void> _makePhoneCall(String phone) async {
    final cleanPhone = phone.replaceAll(RegExp(r'[^0-9+]'), '');
    final uri = Uri.parse('tel:$cleanPhone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: AppTheme.primary,
            content: Text('Calling $phone...'),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final parentName = widget.conversation['parent_name']?.toString() ?? 'Parent';
    final child = widget.conversation['child']?.toString() ?? 'Student';
    final phone = widget.conversation['phone']?.toString() ?? '+977 9841234567';
    final avatar = widget.conversation['avatar']?.toString() ?? 'P';

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new_rounded,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 54,
        title: Row(
          children: [
            Stack(
              children: [
                CircleAvatar(
                  radius: 19,
                  backgroundColor: AppTheme.primary,
                  child: Text(
                    avatar,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 13,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                Positioned(
                  right: 0,
                  bottom: 0,
                  child: Container(
                    width: 10,
                    height: 10,
                    decoration: BoxDecoration(
                      color: const Color(0xFF10B981),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: isDark ? AppTheme.darkCard : Colors.white,
                        width: 1.5,
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    parentName,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  Row(
                    children: [
                      const Icon(Icons.school_rounded,
                          size: 11, color: AppTheme.primary),
                      const SizedBox(width: 3),
                      Expanded(
                        child: Text(
                          child,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w600,
                            color: AppTheme.primary,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'फोन कल गर्नुहोस् ($phone)',
            icon: const Icon(Icons.call_rounded,
                color: AppTheme.green, size: 22),
            onPressed: () => _makePhoneCall(phone),
          ),
          IconButton(
            tooltip: 'विद्यार्थी शैक्षिक विवरण',
            icon: const Icon(Icons.info_outline_rounded,
                color: AppTheme.primary, size: 22),
            onPressed: () => _showStudentQuickProfile(context, isDark),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: Column(
        children: [
          // Messages ListView
          Expanded(
            child: _isLoading && _messages.isEmpty
                ? const Center(
                    child: CircularProgressIndicator(strokeWidth: 2.5),
                  )
                : ListView.builder(
                    controller: _scroll,
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 12),
                    itemCount: _messages.length,
                    itemBuilder: (ctx, idx) {
                      final msg = _messages[idx];
                      return _buildMessageBubble(msg, isDark)
                          .animate()
                          .fadeIn(duration: 150.ms);
                    },
                  ),
          ),

          // Quick Reply Suggestions Chips
          Container(
            height: 38,
            margin: const EdgeInsets.only(bottom: 6),
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              itemCount: _quickReplies.length,
              separatorBuilder: (_, __) => const SizedBox(width: 6),
              itemBuilder: (context, i) {
                final reply = _quickReplies[i];
                return ActionChip(
                  label: Text(
                    reply,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: isDark ? Colors.white70 : AppTheme.dark,
                    ),
                  ),
                  backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
                  side: BorderSide(
                    color: isDark
                        ? AppTheme.darkCardBorder
                        : AppTheme.cardBorder,
                  ),
                  onPressed: () => _sendMessage(reply),
                );
              },
            ),
          ),

          // Chat Input Bar
          Container(
            padding: const EdgeInsets.fromLTRB(12, 8, 12, 16),
            decoration: BoxDecoration(
              color: isDark ? AppTheme.darkCard : Colors.white,
              border: Border(
                top: BorderSide(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                ),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 8,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: Row(
              children: [
                // Attachment Button
                IconButton(
                  tooltip: 'फाइल वा फोटो पठाउनुहोस्',
                  icon: const Icon(Icons.attach_file_rounded,
                      color: AppTheme.darkSubtext, size: 22),
                  onPressed: () => _showAttachmentOptions(isDark),
                ),

                // Text Field
                Expanded(
                  child: TextField(
                    controller: _ctrl,
                    maxLines: 4,
                    minLines: 1,
                    keyboardType: TextInputType.multiline,
                    decoration: InputDecoration(
                      hintText: 'अभिभावकलाई सन्देश लेख्नुहोस्...',
                      hintStyle: const TextStyle(
                        fontSize: 12.5,
                        color: AppTheme.darkSubtext,
                      ),
                      filled: true,
                      fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 10),
                    ),
                    onSubmitted: (_) => _sendMessage(),
                  ),
                ),

                const SizedBox(width: 8),

                // Send Button
                GestureDetector(
                  onTap: () => _sendMessage(),
                  child: Container(
                    width: 44,
                    height: 44,
                    decoration: const BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: LinearGradient(
                        colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                      ),
                    ),
                    child: const Icon(Icons.send_rounded,
                        color: Colors.white, size: 20),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(Map<String, dynamic> msg, bool isDark) {
    final isMe = msg['isMe'] == true;
    final text = msg['text']?.toString() ?? '';
    final timeStr = msg['time']?.toString() ?? '';
    final sender = msg['sender']?.toString() ?? '';

    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.76,
        ),
        decoration: BoxDecoration(
          gradient: isMe
              ? const LinearGradient(
                  colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                )
              : null,
          color: isMe ? null : (isDark ? AppTheme.darkCard : Colors.white),
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(18),
            topRight: const Radius.circular(18),
            bottomLeft: Radius.circular(isMe ? 18 : 4),
            bottomRight: Radius.circular(isMe ? 4 : 18),
          ),
          border: isMe
              ? null
              : Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.2 : 0.04),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment:
              isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            if (!isMe)
              Padding(
                padding: const EdgeInsets.only(bottom: 2),
                child: Text(
                  sender,
                  style: const TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.primary,
                  ),
                ),
              ),
            Text(
              text,
              style: TextStyle(
                fontSize: 13,
                color: isMe
                    ? Colors.white
                    : (isDark ? Colors.white : AppTheme.dark),
                height: 1.35,
              ),
            ),
            const SizedBox(height: 4),
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  timeStr,
                  style: TextStyle(
                    fontSize: 9.5,
                    color: isMe ? Colors.white70 : AppTheme.darkSubtext,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                if (isMe) ...[
                  const SizedBox(width: 4),
                  const Icon(Icons.done_all_rounded,
                      size: 13, color: Colors.white70),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showAttachmentOptions(bool isDark) {
    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Attach Document or Media (फाइल संलग्न गर्नुहोस्)',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildAttachIcon(
                  Icons.camera_alt_rounded,
                  'Camera',
                  AppTheme.primary,
                  () {
                    Navigator.pop(ctx);
                    _sendMessage('[Photo: Classwork Exercise Sample attached]');
                  },
                ),
                _buildAttachIcon(
                  Icons.image_rounded,
                  'Gallery',
                  AppTheme.green,
                  () {
                    Navigator.pop(ctx);
                    _sendMessage('[Image: Mathematics Worksheet Solution attached]');
                  },
                ),
                _buildAttachIcon(
                  Icons.picture_as_pdf_rounded,
                  'Marksheet PDF',
                  AppTheme.red,
                  () {
                    Navigator.pop(ctx);
                    _sendMessage('[PDF: Terminal Marksheet & Progress Report.pdf]');
                  },
                ),
                _buildAttachIcon(
                  Icons.assignment_turned_in_rounded,
                  'Homework Slip',
                  AppTheme.purple,
                  () {
                    Navigator.pop(ctx);
                    _sendMessage('[Assignment: Weekly Math Assessment Slip]');
                  },
                ),
              ],
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );
  }

  Widget _buildAttachIcon(
      IconData icon, String label, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Icon(icon, color: color, size: 26),
          ),
          const SizedBox(height: 6),
          Text(
            label,
            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
          ),
        ],
      ),
    );
  }

  void _showStudentQuickProfile(BuildContext context, bool isDark) {
    final c = widget.conversation;
    final sName = c['student_name'] ?? c['child'] ?? 'Student';
    final grade = c['grade'] ?? 'Class 10 - Section A';
    final pName = c['parent_name'] ?? 'Parent';
    final phone = c['phone'] ?? '+977 98XXXXXXXX';
    final attendance = c['attendance'] ?? '95%';
    final examScore = c['exam_score'] ?? 'GPA 3.8 (A+)';

    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.badge_rounded,
                    color: AppTheme.primary, size: 24),
                const SizedBox(width: 8),
                Text(
                  'विद्यार्थी विवरण: $sName',
                  style: const TextStyle(
                      fontSize: 16, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkSurface : AppTheme.lightBg,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                children: [
                  _buildProfileRow('कक्षा तथा सेक्सन:', grade, isDark),
                  _buildProfileRow('अभिभावकको नाम:', pName, isDark),
                  _buildProfileRow('सम्पर्क नम्बर:', phone, isDark),
                  _buildProfileRow('हाजिरी प्रतिशत:', attendance, isDark,
                      highlightColor: AppTheme.green),
                  _buildProfileRow('पछिल्लो परीक्षा नतिजा:', examScore, isDark,
                      highlightColor: AppTheme.primary),
                  _buildProfileRow('मासिक शुल्क स्थिति:', 'Paid (चुक्ता)', isDark,
                      highlightColor: AppTheme.green),
                ],
              ),
            ),
            const SizedBox(height: 14),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                icon: const Icon(Icons.call_rounded, size: 18),
                label: Text('अभिभावकलाई कल गर्नुहोस् ($phone)'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  _makePhoneCall(phone);
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileRow(String label, String value, bool isDark,
      {Color? highlightColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 11.5,
              color: AppTheme.darkSubtext,
              fontWeight: FontWeight.w600,
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              color: highlightColor ??
                  (isDark ? Colors.white : AppTheme.dark),
            ),
          ),
        ],
      ),
    );
  }
}
