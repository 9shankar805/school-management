import 'dart:convert';
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';

class LeaveApplicationScreen extends StatefulWidget {
  const LeaveApplicationScreen({super.key});

  @override
  State<LeaveApplicationScreen> createState() => _LeaveApplicationScreenState();
}

class _LeaveApplicationScreenState extends State<LeaveApplicationScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  String _selectedFilter = 'All';

  // Leave data
  List<Map<String, dynamic>> _myLeaves = [];
  List<Map<String, dynamic>> _balances = [];
  List<Map<String, dynamic>> _leaveTypes = [];
  List<Map<String, dynamic>> _substitutes = [];
  Map<String, dynamic> _summary = {};
  String _academicYear = '2081/82 BS';

  static const String _cacheKey = 'cached_teacher_leaves_data';

  @override
  void initState() {
    super.initState();
    _loadLeaveData();
  }

  Future<void> _loadLeaveData() async {
    // 1. Instant load from local cache if available
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedJson = prefs.getString(_cacheKey);
      if (cachedJson != null && mounted) {
        final decoded = jsonDecode(cachedJson) as Map<String, dynamic>;
        _applyData(decoded);
      }
    } catch (_) {}

    // 2. Fetch fresh from backend
    final res = await _apiService.getLeavesOverview();
    if (!mounted) return;

    if (res.success && res.data != null) {
      final data = res.data!;
      _applyData(data);

      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_cacheKey, jsonEncode(data));
      } catch (_) {}
    } else if (_myLeaves.isEmpty) {
      _loadFallbackData();
    }

    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  void _applyData(Map<String, dynamic> data) {
    setState(() {
      _academicYear = data['academic_year']?.toString() ?? '2081/82 BS';

      if (data['summary'] is Map) {
        _summary = Map<String, dynamic>.from(data['summary']);
      }

      if (data['balances'] is List) {
        _balances = (data['balances'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }

      if (data['leaves'] is List) {
        _myLeaves = (data['leaves'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }

      if (data['leave_types'] is List) {
        _leaveTypes = (data['leave_types'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }

      if (data['substitutes'] is List) {
        _substitutes = (data['substitutes'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }
    });
  }

  void _loadFallbackData() {
    setState(() {
      _academicYear = '2081/82 BS';
      _balances = [
        {'id': 1, 'code': 'CL', 'leave_type': 'Casual (आकस्मिक)', 'total_days': 12, 'used_days': 3, 'remaining_days': 9, 'color': '#0F4C81'},
        {'id': 2, 'code': 'SL', 'leave_type': 'Sick (बिरामी)', 'total_days': 12, 'used_days': 2, 'remaining_days': 10, 'color': '#10B981'},
        {'id': 3, 'code': 'FL', 'leave_type': 'Festival (पर्व)', 'total_days': 20, 'used_days': 0, 'remaining_days': 20, 'color': '#F59E0B'},
        {'id': 4, 'code': 'AL', 'leave_type': 'Academic (शैक्षिक)', 'total_days': 6, 'used_days': 2, 'remaining_days': 4, 'color': '#8B5CF6'},
        {'id': 5, 'code': 'ML', 'leave_type': 'Mourning (किरिया)', 'total_days': 15, 'used_days': 0, 'remaining_days': 15, 'color': '#64748B'},
      ];

      _myLeaves = [
        {
          'id': 101,
          'leave_type': 'Casual Leave (आकस्मिक विदा)',
          'leave_type_id': 1,
          'start_date': '2026-11-05',
          'end_date': '2026-11-06',
          'nepali_start_date': '२०८१ कार्तिक २०',
          'nepali_end_date': '२०८१ कार्तिक २१',
          'total_days': 2,
          'status': 'pending',
          'status_label': 'प्रतिक्षारत (Pending Approval)',
          'reason': 'Family religious ceremony & household obligations.',
          'substitute_teacher': 'Mr. Ramesh Bhattarai (Mathematics)',
          'contact_number': '9841234567',
          'reviewer_notes': 'Forwarded to Principal for official sign-off.',
          'reviewed_by': null,
          'applied_on': '२०८१ कार्तिक १५',
        },
        {
          'id': 102,
          'leave_type': 'Sick Leave (बिरामी विदा)',
          'leave_type_id': 2,
          'start_date': '2026-09-25',
          'end_date': '2026-09-26',
          'nepali_start_date': '२०८१ असोज १०',
          'nepali_end_date': '२०८१ असोज ११',
          'total_days': 2,
          'status': 'approved',
          'status_label': 'स्वीकृत (Approved)',
          'reason': 'Seasonal viral flu and prescribed medical bed rest.',
          'substitute_teacher': 'Mrs. Gita Sharma (Science)',
          'contact_number': '9841234567',
          'reviewer_notes': 'Sanctioned with full medical allowance pay.',
          'reviewed_by': 'Dr. Narayan Prasad (Principal)',
          'applied_on': '२०८१ असोज ०९',
        },
        {
          'id': 103,
          'leave_type': 'Casual Leave (आकस्मिक विदा)',
          'leave_type_id': 1,
          'start_date': '2026-08-27',
          'end_date': '2026-08-27',
          'nepali_start_date': '२०८१ भदौ १२',
          'nepali_end_date': '२०८१ भदौ १२',
          'total_days': 1,
          'status': 'approved',
          'status_label': 'स्वीकृत (Approved)',
          'reason': 'Urgent personal work at Land Revenue Office (मालपोत कार्यालय).',
          'substitute_teacher': 'Mr. Bimal Adhikari (English)',
          'contact_number': '9841234567',
          'reviewer_notes': 'Class periods coordinated and approved.',
          'reviewed_by': 'Dr. Narayan Prasad (Principal)',
          'applied_on': '२०८१ भदौ ११',
        },
      ];

      _leaveTypes = [
        {'id': 1, 'name': 'Casual Leave (आकस्मिक विदा)', 'days_allowed': 12},
        {'id': 2, 'name': 'Sick Leave (बिरामी विदा)', 'days_allowed': 12},
        {'id': 3, 'name': 'Festival Leave (पर्व विदा)', 'days_allowed': 20},
        {'id': 4, 'name': 'Special Academic Leave (अध्ययन/तालिम)', 'days_allowed': 6},
        {'id': 5, 'name': 'Mourning / Kiriya Leave (किरिया विदा)', 'days_allowed': 15},
      ];

      _substitutes = [
        {'id': 201, 'name': 'Mr. Ramesh Bhattarai', 'display': 'Mr. Ramesh Bhattarai (Mathematics)'},
        {'id': 202, 'name': 'Mrs. Gita Sharma', 'display': 'Mrs. Gita Sharma (Science)'},
        {'id': 203, 'name': 'Mr. Bimal Adhikari', 'display': 'Mr. Bimal Adhikari (English)'},
        {'id': 204, 'name': 'Mrs. Sunita Thapa', 'display': 'Mrs. Sunita Thapa (Social Studies)'},
      ];

      _summary = {
        'total_allowed': 36,
        'total_used': 7,
        'total_remaining': 29,
        'pending_requests': 1,
        'approved_requests': 2,
      };
    });
  }

  List<Map<String, dynamic>> get _filteredLeaves {
    if (_selectedFilter == 'All') return _myLeaves;
    final f = _selectedFilter.toLowerCase();
    return _myLeaves.where((l) {
      final s = (l['status']?.toString() ?? '').toLowerCase();
      return s.contains(f);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Leave Management (बिदा अभिलेख)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Staff Leave Balance & Requests • $_academicYear',
              style: const TextStyle(
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
              _loadLeaveData();
            },
          ),
          Container(
            margin: const EdgeInsets.only(right: 14),
            child: ElevatedButton.icon(
              onPressed: () => _showApplyModal(isDark),
              icon: const Icon(Icons.add_rounded, size: 18),
              label: const Text(
                'Apply विदा',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.purple,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],
      ),
      body: _isLoading && _myLeaves.isEmpty
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(strokeWidth: 2.5),
                  SizedBox(height: 14),
                  Text(
                    'बिदा विवरण लोड हुँदैछ...',
                    style: TextStyle(fontSize: 12, color: AppTheme.darkSubtext),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadLeaveData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: const EdgeInsets.fromLTRB(16, 6, 16, 90),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 1. Leave Balances Horizontal Carousel
                    _buildBalancesSection(isDark),

                    const SizedBox(height: 16),

                    // 2. Summary Strip
                    _buildSummaryStrip(isDark),

                    const SizedBox(height: 20),

                    // 3. Filter Chips
                    _buildFilterSection(isDark),

                    const SizedBox(height: 14),

                    // 4. Applications List
                    _buildLeavesList(isDark),
                  ],
                ),
              ),
            ),
    );
  }

  // =========================================================================
  // 1. LEAVE BALANCES CAROUSEL
  // =========================================================================
  Widget _buildBalancesSection(bool isDark) {
    if (_balances.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Leave Balances (विधा सञ्चिति विवरण)',
              style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'FY $_academicYear',
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: AppTheme.darkSubtext,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        SizedBox(
          height: 105,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            itemCount: _balances.length,
            separatorBuilder: (_, __) => const SizedBox(width: 10),
            itemBuilder: (ctx, i) {
              final b = _balances[i];
              return _buildBalanceCard(b, isDark);
            },
          ),
        ),
      ],
    );
  }

  Widget _buildBalanceCard(Map<String, dynamic> b, bool isDark) {
    final title = b['leave_type']?.toString() ?? 'Leave';
    final remaining = b['remaining_days'] ?? 0;
    final total = b['total_days'] ?? 12;
    final used = b['used_days'] ?? 0;
    final colorHex = b['color']?.toString() ?? '#0F4C81';
    final cardColor = _parseColor(colorHex);

    final ratio = total > 0 ? (remaining / total).clamp(0.0, 1.0) : 1.0;

    return Container(
      width: 145,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: isDark ? Colors.white70 : AppTheme.dark,
                  ),
                ),
              ),
              Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(
                  color: cardColor,
                  shape: BoxShape.circle,
                ),
              ),
            ],
          ),
          Row(
            crossAxisAlignment: CrossAxisAlignment.baseline,
            textBaseline: TextBaseline.alphabetic,
            children: [
              Text(
                '$remaining',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w900,
                  color: cardColor,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(width: 4),
              Text(
                '/$total दिन बाँकी',
                style: const TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.darkSubtext,
                ),
              ),
            ],
          ),
          // Progress Bar
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: ratio,
              minHeight: 4,
              backgroundColor: cardColor.withOpacity(0.15),
              valueColor: AlwaysStoppedAnimation<Color>(cardColor),
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // 2. SUMMARY STRIP
  // =========================================================================
  Widget _buildSummaryStrip(bool isDark) {
    final allowed = _summary['total_allowed'] ?? 36;
    final used = _summary['total_used'] ?? 7;
    final remaining = _summary['total_remaining'] ?? 29;
    final pending = _summary['pending_requests'] ?? 0;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
        ),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _buildSummaryItem('कुल अधिकार', '$allowed दिन', AppTheme.primary, isDark),
          Container(width: 1, height: 26, color: isDark ? AppTheme.darkSurface : AppTheme.cardBorder),
          _buildSummaryItem('लिएको विदा', '$used दिन', AppTheme.orange, isDark),
          Container(width: 1, height: 26, color: isDark ? AppTheme.darkSurface : AppTheme.cardBorder),
          _buildSummaryItem('सञ्चित बाँकी', '$remaining दिन', AppTheme.green, isDark),
          Container(width: 1, height: 26, color: isDark ? AppTheme.darkSurface : AppTheme.cardBorder),
          _buildSummaryItem('प्रतिक्षारत', '$pending वटा', AppTheme.purple, isDark),
        ],
      ),
    );
  }

  Widget _buildSummaryItem(String label, String value, Color color, bool isDark) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w900,
            color: color,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: const TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w600,
            color: AppTheme.darkSubtext,
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // 3. FILTER TABS
  // =========================================================================
  Widget _buildFilterSection(bool isDark) {
    final filters = [
      {'key': 'All', 'label': 'सबै (All)'},
      {'key': 'Pending', 'label': 'प्रतिक्षारत (Pending)'},
      {'key': 'Approved', 'label': 'स्वीकृत (Approved)'},
      {'key': 'Rejected', 'label': 'अस्वीकृत'},
      {'key': 'Cancelled', 'label': 'रद्द (Cancelled)'},
    ];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      physics: const BouncingScrollPhysics(),
      child: Row(
        children: filters.map((f) {
          final isSelected = _selectedFilter == f['key'];
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              label: Text(
                f['label']!,
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                  color: isSelected
                      ? Colors.white
                      : (isDark ? Colors.white70 : AppTheme.dark),
                ),
              ),
              selected: isSelected,
              selectedColor: AppTheme.purple,
              backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
              side: BorderSide(
                color: isSelected
                    ? AppTheme.purple
                    : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
              ),
              onSelected: (_) {
                setState(() => _selectedFilter = f['key']!);
              },
            ),
          );
        }).toList(),
      ),
    );
  }

  // =========================================================================
  // 4. LEAVE APPLICATIONS LIST
  // =========================================================================
  Widget _buildLeavesList(bool isDark) {
    final list = _filteredLeaves;

    if (list.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
        alignment: Alignment.center,
        child: Column(
          children: [
            Icon(Icons.event_busy_rounded,
                size: 48,
                color: isDark ? AppTheme.darkSubtext : Colors.grey.shade400),
            const SizedBox(height: 12),
            Text(
              _selectedFilter == 'All'
                  ? 'कुनै विदा निवेदन फेला परेन।'
                  : '$_selectedFilter अवस्थामा कुनै निवेदन छैन।',
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: AppTheme.darkSubtext,
              ),
            ),
          ],
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SectionHeader(
          title: 'Leave Requests History (निवेदन अभिलेख)',
          subtitle: '${list.length} records • Tap card to view approval timeline',
        ),
        const SizedBox(height: 10),
        ...list.asMap().entries.map((entry) {
          final index = entry.key;
          final leave = entry.value;
          return _buildLeaveCard(leave, index, isDark);
        }),
      ],
    );
  }

  Widget _buildLeaveCard(Map<String, dynamic> leave, int index, bool isDark) {
    final status = (leave['status']?.toString() ?? 'pending').toLowerCase();
    final isApproved = status == 'approved';
    final isPending = status == 'pending';
    final isRejected = status == 'rejected';

    final Color statusColor = isApproved
        ? AppTheme.green
        : (isPending
            ? AppTheme.orange
            : (isRejected ? AppTheme.red : AppTheme.grey));

    final String statusText = leave['status_label']?.toString() ??
        (isApproved
            ? 'स्वीकृत (Approved)'
            : (isPending
                ? 'प्रतिक्षारत (Pending)'
                : (isRejected ? 'अस्वीकृत (Rejected)' : 'रद्द गरिएको')));

    final leaveType = leave['leave_type']?.toString() ?? 'Casual Leave';
    final nepaliStart = leave['nepali_start_date'] ?? leave['start_date'] ?? '';
    final nepaliEnd = leave['nepali_end_date'] ?? leave['end_date'] ?? '';
    final days = leave['total_days'] ?? 1;
    final reason = leave['reason']?.toString() ?? '';
    final substitute = leave['substitute_teacher']?.toString() ?? 'Not assigned';
    final appliedOn = leave['applied_on'] ?? leave['created_at'] ?? '';
    final leaveId = leave['id'];

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _showLeaveDetailModal(leave, isDark),
          borderRadius: BorderRadius.circular(20),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header Row
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        isApproved
                            ? Icons.check_circle_rounded
                            : (isPending
                                ? Icons.hourglass_top_rounded
                                : (isRejected
                                    ? Icons.cancel_rounded
                                    : Icons.block_rounded)),
                        color: statusColor,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            leaveType,
                            style: const TextStyle(
                              fontWeight: FontWeight.w800,
                              fontSize: 13.5,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '$days दिन ($nepaliStart देखि $nepaliEnd सम्म)',
                            style: const TextStyle(
                              color: AppTheme.darkSubtext,
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        statusText,
                        style: TextStyle(
                          color: statusColor,
                          fontWeight: FontWeight.w800,
                          fontSize: 10,
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 12),

                // Reason
                Text(
                  'कारण: $reason',
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark ? Colors.white70 : AppTheme.dark,
                    height: 1.35,
                  ),
                ),

                const SizedBox(height: 8),

                // Substitute Teacher
                Row(
                  children: [
                    const Icon(Icons.swap_horiz_rounded,
                        size: 15, color: AppTheme.primary),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        'सट्टा शिक्षक: $substitute',
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

                const SizedBox(height: 8),
                Divider(
                    height: 1,
                    color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                const SizedBox(height: 8),

                // Footer Row: Applied Date and Cancel Button (if pending)
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'निवेदन मिति: $appliedOn',
                      style: const TextStyle(
                        fontSize: 10.5,
                        color: AppTheme.darkSubtext,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    if (isPending)
                      TextButton.icon(
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 2),
                          visualDensity: VisualDensity.compact,
                          foregroundColor: AppTheme.red,
                        ),
                        icon: const Icon(Icons.cancel_outlined, size: 14),
                        label: const Text(
                          'रद्द गर्नुहोस् (Cancel)',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        onPressed: () => _confirmCancelLeave(leaveId, leaveType),
                      )
                    else
                      const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'विवरण हेर्नुहोस्',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: AppTheme.primary,
                            ),
                          ),
                          SizedBox(width: 2),
                          Icon(Icons.arrow_forward_ios_rounded,
                              size: 10, color: AppTheme.primary),
                        ],
                      ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    ).animate().fadeIn(delay: (index * 40).ms);
  }

  // =========================================================================
  // 5. APPLY LEAVE MODAL (आवेदन फाराम)
  // =========================================================================
  void _showApplyModal(bool isDark) {
    Map<String, dynamic> selectedType = _leaveTypes.isNotEmpty
        ? _leaveTypes.first
        : {'id': 1, 'name': 'Casual Leave (आकस्मिक विदा)', 'days_allowed': 12};

    String selectedSubstitute = _substitutes.isNotEmpty
        ? (_substitutes.first['display'] ?? _substitutes.first['name'])
        : 'Mr. Ramesh Bhattarai (Mathematics)';

    DateTime startDate = DateTime.now().add(const Duration(days: 1));
    DateTime endDate = DateTime.now().add(const Duration(days: 2));

    final reasonCtrl = TextEditingController();
    final contactCtrl = TextEditingController(text: '9841234567');
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (modalCtx, setModalState) {
          final totalDays = max(1, endDate.difference(startDate).inDays + 1);
          final nepaliStart = _formatDateToNepaliBS(startDate);
          final nepaliEnd = _formatDateToNepaliBS(endDate);

          return Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(modalCtx).viewInsets.bottom,
            ),
            child: Container(
              constraints: BoxConstraints(
                maxHeight: MediaQuery.of(modalCtx).size.height * 0.90,
              ),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(26)),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Drag Handle
                  Container(
                    margin: const EdgeInsets.only(top: 10, bottom: 8),
                    width: 44,
                    height: 4,
                    decoration: BoxDecoration(
                      color: isDark ? AppTheme.darkSurface : Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),

                  // Header
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.event_available_rounded,
                                color: AppTheme.purple, size: 22),
                            SizedBox(width: 8),
                            Text(
                              'Apply Leave (विदा निवेदन पेश गर्नुहोस्)',
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.w900),
                            ),
                          ],
                        ),
                        IconButton(
                          icon: const Icon(Icons.close_rounded, size: 22),
                          onPressed: () => Navigator.pop(modalCtx),
                        ),
                      ],
                    ),
                  ),
                  const Divider(height: 1),

                  // Form Fields
                  Expanded(
                    child: SingleChildScrollView(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // 1. Leave Type Selector
                          const Text(
                            'विदाको प्रकार (Leave Category):',
                            style: TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 6),
                          DropdownButtonFormField<Map<String, dynamic>>(
                            value: selectedType,
                            isExpanded: true,
                            decoration: InputDecoration(
                              filled: true,
                              fillColor: isDark
                                  ? AppTheme.darkSurface
                                  : AppTheme.lightBg,
                              contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 14, vertical: 12),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide.none,
                              ),
                            ),
                            items: _leaveTypes.map((t) {
                              return DropdownMenuItem(
                                value: t,
                                child: Text(
                                  t['name']?.toString() ?? 'Leave',
                                  style: const TextStyle(fontSize: 13),
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setModalState(() => selectedType = val);
                              }
                            },
                          ),

                          const SizedBox(height: 16),

                          // 2. Interactive Date Range Selector
                          const Text(
                            'विदा अवधि (Leave Date Range):',
                            style: TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 6),
                          Row(
                            children: [
                              // Start Date
                              Expanded(
                                child: GestureDetector(
                                  onTap: () async {
                                    final picked = await showDatePicker(
                                      context: modalCtx,
                                      initialDate: startDate,
                                      firstDate: DateTime.now().subtract(
                                          const Duration(days: 7)),
                                      lastDate: DateTime.now()
                                          .add(const Duration(days: 120)),
                                    );
                                    if (picked != null) {
                                      setModalState(() {
                                        startDate = picked;
                                        if (endDate.isBefore(startDate)) {
                                          endDate = startDate;
                                        }
                                      });
                                    }
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: isDark
                                          ? AppTheme.darkSurface
                                          : AppTheme.lightBg,
                                      borderRadius: BorderRadius.circular(14),
                                      border: Border.all(
                                        color: AppTheme.purple.withOpacity(0.3),
                                      ),
                                    ),
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        const Text(
                                          'शुरु मिति (From Date)',
                                          style: TextStyle(
                                            fontSize: 10,
                                            color: AppTheme.darkSubtext,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          nepaliStart,
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w800,
                                            fontSize: 12.5,
                                          ),
                                        ),
                                        Text(
                                          DateFormat('yyyy-MM-dd')
                                              .format(startDate),
                                          style: const TextStyle(
                                            fontSize: 10,
                                            color: AppTheme.darkSubtext,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),

                              // End Date
                              Expanded(
                                child: GestureDetector(
                                  onTap: () async {
                                    final picked = await showDatePicker(
                                      context: modalCtx,
                                      initialDate: endDate,
                                      firstDate: startDate,
                                      lastDate: DateTime.now()
                                          .add(const Duration(days: 120)),
                                    );
                                    if (picked != null) {
                                      setModalState(() => endDate = picked);
                                    }
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: isDark
                                          ? AppTheme.darkSurface
                                          : AppTheme.lightBg,
                                      borderRadius: BorderRadius.circular(14),
                                      border: Border.all(
                                        color: AppTheme.purple.withOpacity(0.3),
                                      ),
                                    ),
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        const Text(
                                          'अन्तिम मिति (To Date)',
                                          style: TextStyle(
                                            fontSize: 10,
                                            color: AppTheme.darkSubtext,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          nepaliEnd,
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w800,
                                            fontSize: 12.5,
                                          ),
                                        ),
                                        Text(
                                          DateFormat('yyyy-MM-dd')
                                              .format(endDate),
                                          style: const TextStyle(
                                            fontSize: 10,
                                            color: AppTheme.darkSubtext,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ),

                          const SizedBox(height: 8),

                          // Total Days badge
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppTheme.purple.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.info_outline_rounded,
                                    size: 14, color: AppTheme.purple),
                                const SizedBox(width: 6),
                                Text(
                                  'कुल विदा दिन: $totalDays दिन (सञ्चित विदाबाट कट्टी हुनेछ)',
                                  style: const TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w800,
                                    color: AppTheme.purple,
                                  ),
                                ),
                              ],
                            ),
                          ),

                          const SizedBox(height: 16),

                          // 3. Substitute Teacher Dropdown
                          const Text(
                            'सट्टा शिक्षक (Period Substitute Colleague):',
                            style: TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 6),
                          DropdownButtonFormField<String>(
                            value: selectedSubstitute,
                            isExpanded: true,
                            decoration: InputDecoration(
                              filled: true,
                              fillColor: isDark
                                  ? AppTheme.darkSurface
                                  : AppTheme.lightBg,
                              contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 14, vertical: 12),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide.none,
                              ),
                            ),
                            items: _substitutes.map<DropdownMenuItem<String>>((s) {
                              final text = s['display']?.toString() ?? s['name']?.toString() ?? 'Colleague';
                              return DropdownMenuItem<String>(
                                value: text,
                                child: Text(
                                  text,
                                  style: const TextStyle(fontSize: 12.5),
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setModalState(() => selectedSubstitute = val);
                              }
                            },
                          ),

                          const SizedBox(height: 16),

                          // 4. Emergency Contact Number
                          const Text(
                            'विदा अवधिमा सम्पर्क नम्बर (Emergency Contact):',
                            style: TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 6),
                          TextField(
                            controller: contactCtrl,
                            keyboardType: TextInputType.phone,
                            decoration: InputDecoration(
                              prefixIcon: const Icon(Icons.phone_rounded,
                                  size: 18, color: AppTheme.purple),
                              hintText: 'मोबाइल नम्बर...',
                              filled: true,
                              fillColor: isDark
                                  ? AppTheme.darkSurface
                                  : AppTheme.lightBg,
                              contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 14, vertical: 12),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide.none,
                              ),
                            ),
                          ),

                          const SizedBox(height: 16),

                          // 5. Reason Field with Quick Suggestion Chips
                          const Text(
                            'विदा लिनुको कारण (Reason for Leave):',
                            style: TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 6),
                          TextField(
                            controller: reasonCtrl,
                            maxLines: 3,
                            decoration: InputDecoration(
                              hintText:
                                  'प्रधानाध्यापक वा प्रशासन शाखाका लागि स्पष्ट कारण खुलाउनुहोस्...',
                              hintStyle: const TextStyle(fontSize: 12),
                              filled: true,
                              fillColor: isDark
                                  ? AppTheme.darkSurface
                                  : AppTheme.lightBg,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide.none,
                              ),
                            ),
                          ),

                          const SizedBox(height: 8),

                          // Quick Chips
                          Wrap(
                            spacing: 6,
                            runSpacing: 4,
                            children: [
                              'घरायसी काम',
                              'स्वास्थ्य उपचार / बिरामी',
                              'पारिवारिक पूजा / उत्सव',
                              'अध्ययन / तालिम',
                            ].map((r) {
                              return ActionChip(
                                label: Text(r),
                                labelStyle: const TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w700,
                                ),
                                onPressed: () {
                                  reasonCtrl.text = r;
                                },
                              );
                            }).toList(),
                          ),

                          const SizedBox(height: 24),

                          // 6. Submit Button
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
                              onPressed: isSubmitting
                                  ? null
                                  : () async {
                                      final reason = reasonCtrl.text.trim();
                                      if (reason.isEmpty) {
                                        ScaffoldMessenger.of(context)
                                            .showSnackBar(
                                          const SnackBar(
                                            backgroundColor: AppTheme.red,
                                            content: Text(
                                                'कृपया विदा लिनुको कारण प्रविष्ट गर्नुहोस्।'),
                                          ),
                                        );
                                        return;
                                      }

                                      setModalState(() => isSubmitting = true);

                                      final postData = {
                                        'leave_type': selectedType['name'],
                                        'leave_type_id': selectedType['id'],
                                        'start_date': DateFormat('yyyy-MM-dd')
                                            .format(startDate),
                                        'end_date': DateFormat('yyyy-MM-dd')
                                            .format(endDate),
                                        'reason': reason,
                                        'substitute_teacher': selectedSubstitute,
                                        'contact_number': contactCtrl.text.trim(),
                                      };

                                      final res = await _apiService
                                          .applyLeave(postData);

                                      if (!modalCtx.mounted) return;
                                      Navigator.pop(modalCtx);

                                      if (res.success) {
                                        ScaffoldMessenger.of(context)
                                            .showSnackBar(
                                          const SnackBar(
                                            backgroundColor: AppTheme.green,
                                            content: Text(
                                                'Leave application submitted to Principal Office! प्रधानाध्यापक शाखामा पठाइयो ✅'),
                                          ),
                                        );
                                        // Refresh leaves list
                                        _loadLeaveData();
                                      } else {
                                        ScaffoldMessenger.of(context)
                                            .showSnackBar(
                                          SnackBar(
                                            backgroundColor: AppTheme.red,
                                            content: Text(
                                                'आवेदन पठाउन असफल: ${res.message}'),
                                          ),
                                        );
                                      }
                                    },
                              child: isSubmitting
                                  ? const SizedBox(
                                      width: 22,
                                      height: 22,
                                      child: CircularProgressIndicator(
                                        color: Colors.white,
                                        strokeWidth: 2,
                                      ),
                                    )
                                  : const Text(
                                      'Submit Application (निवेदन पेश गर्नुहोस्)',
                                      style: TextStyle(
                                        fontWeight: FontWeight.w900,
                                        fontSize: 14,
                                      ),
                                    ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 6. LEAVE DETAIL MODAL (विस्तृत विवरण तथा समयतालिका)
  // =========================================================================
  void _showLeaveDetailModal(Map<String, dynamic> leave, bool isDark) {
    final status = (leave['status']?.toString() ?? 'pending').toLowerCase();
    final isApproved = status == 'approved';
    final isPending = status == 'pending';
    final isRejected = status == 'rejected';

    final Color statusColor = isApproved
        ? AppTheme.green
        : (isPending
            ? AppTheme.orange
            : (isRejected ? AppTheme.red : AppTheme.grey));

    final leaveType = leave['leave_type']?.toString() ?? 'Casual Leave';
    final nepaliStart = leave['nepali_start_date'] ?? leave['start_date'] ?? '';
    final nepaliEnd = leave['nepali_end_date'] ?? leave['end_date'] ?? '';
    final days = leave['total_days'] ?? 1;
    final reason = leave['reason']?.toString() ?? '';
    final substitute = leave['substitute_teacher']?.toString() ?? 'None';
    final contact = leave['contact_number']?.toString() ?? '98XXXXXXXX';
    final notes = leave['reviewer_notes']?.toString() ?? 'No remarks provided.';
    final reviewer = leave['reviewed_by']?.toString() ?? 'Principal Office';
    final appliedOn = leave['applied_on'] ?? leave['created_at'] ?? '';
    final leaveId = leave['id'];

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
              // Handle
              Container(
                margin: const EdgeInsets.only(top: 10, bottom: 8),
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkSurface : Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),

              // Title
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Leave Application Details (विदा विवरण)',
                      style: TextStyle(
                          fontSize: 16, fontWeight: FontWeight.w900),
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
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Status Banner
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: statusColor.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: statusColor.withOpacity(0.3),
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              isApproved
                                  ? Icons.verified_rounded
                                  : (isPending
                                      ? Icons.hourglass_empty_rounded
                                      : Icons.info_outline_rounded),
                              color: statusColor,
                              size: 26,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    leaveType,
                                    style: TextStyle(
                                      fontWeight: FontWeight.w900,
                                      fontSize: 13.5,
                                      color: isDark ? Colors.white : AppTheme.dark,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    leave['status_label']?.toString() ??
                                        status.toUpperCase(),
                                    style: TextStyle(
                                      fontSize: 11.5,
                                      fontWeight: FontWeight.w800,
                                      color: statusColor,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: statusColor,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '$days दिन (Days)',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 11,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Meta details
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkSurface : AppTheme.lightBg,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Column(
                          children: [
                            _buildDetailMetaRow('अवधि (Date Range):',
                                '$nepaliStart देखि $nepaliEnd सम्म', isDark),
                            _buildDetailMetaRow(
                                'निवेदन मिति:', appliedOn.toString(), isDark),
                            _buildDetailMetaRow(
                                'सट्टा शिक्षक (Substitute):', substitute, isDark),
                            _buildDetailMetaRow(
                                'आपतकालीन सम्पर्क:', contact, isDark),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Reason Box
                      const Text(
                        'विदा लिनुको कारण (Reason):',
                        style: TextStyle(
                            fontSize: 12, fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkSurface : AppTheme.lightBg,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          reason,
                          style: TextStyle(
                            fontSize: 12.5,
                            color: isDark ? Colors.white70 : AppTheme.dark,
                            height: 1.4,
                          ),
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Approval Steps Timeline
                      const Text(
                        'प्रमाणीकरण तथा स्वीकृति चरण (Approval Tracker):',
                        style: TextStyle(
                            fontSize: 12, fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 10),

                      _buildTimelineStep(
                        title: '१. निवेदन दर्ता (Application Submitted)',
                        subtitle: 'शिक्षकद्वारा अनलाइन पोर्टलबाट पेश गरिएको',
                        isCompleted: true,
                        isDark: isDark,
                      ),
                      _buildTimelineStep(
                        title: '२. सट्टा शिक्षक समन्वय (Substitute Assigned)',
                        subtitle: 'सहकर्मी $substitute लाई समन्वय गरिएको',
                        isCompleted: true,
                        isDark: isDark,
                      ),
                      _buildTimelineStep(
                        title: '३. प्रशासन समीक्षा (Admin / VP Review)',
                        subtitle: 'उप-प्रधानाध्यापक तथा लेखा शाखाबाट जाँच',
                        isCompleted: isApproved || isRejected,
                        isCurrent: isPending,
                        isDark: isDark,
                      ),
                      _buildTimelineStep(
                        title: '४. प्रधानाध्यापकको निर्णय (Principal Approval)',
                        subtitle: isApproved
                            ? 'प्रधानाध्यापक $reviewer द्वारा स्वीकृत ✅'
                            : (isRejected
                                ? 'प्रधानाध्यापकबाट अस्वीकृत'
                                : 'अन्तिम निर्णय प्रतिक्षारत...'),
                        isCompleted: isApproved,
                        isFailed: isRejected,
                        isLast: true,
                        isDark: isDark,
                      ),

                      const SizedBox(height: 14),

                      // Reviewer Notes
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withOpacity(0.08),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: AppTheme.primary.withOpacity(0.2),
                          ),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(Icons.comment_bank_outlined,
                                size: 18, color: AppTheme.primary),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'प्रशासनिक टिप्पणी (Administrative Remarks):',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w800,
                                      fontSize: 11,
                                      color: AppTheme.primary,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    notes,
                                    style: TextStyle(
                                      fontSize: 11.5,
                                      color: isDark
                                          ? Colors.white70
                                          : AppTheme.dark,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // Bottom Cancel Action if Pending
              if (isPending)
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkSurface : Colors.grey.shade50,
                    border: Border(
                      top: BorderSide(
                        color: isDark
                            ? AppTheme.darkCardBorder
                            : AppTheme.cardBorder,
                      ),
                    ),
                  ),
                  child: SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.red,
                        side: const BorderSide(color: AppTheme.red),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      icon: const Icon(Icons.cancel_rounded, size: 18),
                      label: const Text(
                        'यो निवेदन रद्द गर्नुहोस् (Cancel Request)',
                        style: TextStyle(fontWeight: FontWeight.w800),
                      ),
                      onPressed: () {
                        Navigator.pop(ctx);
                        _confirmCancelLeave(leaveId, leaveType);
                      },
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDetailMetaRow(String label, String value, bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 11,
              color: AppTheme.darkSubtext,
              fontWeight: FontWeight.w600,
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              color: isDark ? Colors.white : AppTheme.dark,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTimelineStep({
    required String title,
    required String subtitle,
    required bool isCompleted,
    bool isCurrent = false,
    bool isFailed = false,
    bool isLast = false,
    required bool isDark,
  }) {
    final color = isFailed
        ? AppTheme.red
        : (isCompleted
            ? AppTheme.green
            : (isCurrent ? AppTheme.orange : AppTheme.grey));

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 18,
              height: 18,
              decoration: BoxDecoration(
                color: color,
                shape: BoxShape.circle,
              ),
              child: Icon(
                isFailed
                    ? Icons.close_rounded
                    : (isCompleted
                        ? Icons.check_rounded
                        : (isCurrent ? Icons.circle : Icons.circle_outlined)),
                color: Colors.white,
                size: 11,
              ),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 28,
                color: isCompleted
                    ? AppTheme.green
                    : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
              ),
          ],
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: isDark ? Colors.white : AppTheme.dark,
                ),
              ),
              Text(
                subtitle,
                style: const TextStyle(
                  fontSize: 10.5,
                  color: AppTheme.darkSubtext,
                ),
              ),
              const SizedBox(height: 8),
            ],
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // 7. CANCEL LEAVE ACTION
  // =========================================================================
  void _confirmCancelLeave(dynamic leaveId, String leaveType) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text(
          'निवेदन रद्द गर्ने पुष्टि गर्नुहोस्',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
        ),
        content: Text(
          'के तपाईं $leaveType को लागि पेश गरिएको विदा निवेदन रद्द गर्न चाहनुहुन्छ?',
          style: const TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            child: const Text('होइन (Back)'),
            onPressed: () => Navigator.pop(ctx),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.red,
              foregroundColor: Colors.white,
            ),
            child: const Text('रद्द गर्नुहोस् (Confirm)'),
            onPressed: () async {
              Navigator.pop(ctx);
              final res = await _apiService.cancelLeave(leaveId);
              if (res.success) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content: Text('Leave application cancelled successfully.'),
                    ),
                  );
                  _loadLeaveData();
                }
              } else {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      backgroundColor: AppTheme.red,
                      content: Text('रद्द गर्न असफल: ${res.message}'),
                    ),
                  );
                }
              }
            },
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // UTILITIES
  // =========================================================================
  static Color _parseColor(String hex) {
    try {
      final clean = hex.replaceAll('#', '');
      if (clean.length == 6) {
        return Color(int.parse('FF$clean', radix: 16));
      }
    } catch (_) {}
    return AppTheme.primary;
  }

  static String _formatDateToNepaliBS(DateTime dt) {
    final m = dt.month;
    final d = dt.day;

    final nepaliMonths = {
      1: 'माघ', 2: 'फागुन', 3: 'चैत',
      4: 'वैशाख', 5: 'जेठ', 6: 'असार',
      7: 'साउन', 8: 'भदौ', 9: 'असोज',
      10: 'कार्तिक', 11: 'मंसिर', 12: 'पुस',
    };

    final nepaliDigits = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
    final dStr = d.toString();
    var nepDay = '';
    for (int i = 0; i < dStr.length; i++) {
      nepDay += nepaliDigits[int.parse(dStr[i])];
    }

    final mName = nepaliMonths[m] ?? 'महिना';
    return '२०८१ $mName $nepDay';
  }
}
