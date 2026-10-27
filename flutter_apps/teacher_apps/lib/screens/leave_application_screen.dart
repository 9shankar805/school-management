import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';

class LeaveApplicationScreen extends StatefulWidget {
  const LeaveApplicationScreen({super.key});

  @override
  State<LeaveApplicationScreen> createState() => _LeaveApplicationScreenState();
}

class _LeaveApplicationScreenState extends State<LeaveApplicationScreen> {
  final List<Map<String, dynamic>> _myLeaves = [
    {
      'type': 'Casual Leave (आकस्मिक विदा)',
      'startDate': '२०८१ असोज १५',
      'endDate': '२०८१ असोज १६',
      'days': 2,
      'reason': 'Family religious puja and festival prep',
      'substitute': 'Mr. Ramesh Bhattarai (Maths)',
      'status': 'Approved',
      'appliedOn': '२०८१ असोज १०',
    },
    {
      'type': 'Sick Leave (बिरामी विदा)',
      'startDate': '२०८१ भदौ ०२',
      'endDate': '२०८१ भदौ ०३',
      'days': 2,
      'reason': 'Medical checkup and viral fever recovery',
      'substitute': 'Mrs. Gita Sharma (Science)',
      'status': 'Approved',
      'appliedOn': '२०८१ भदौ ०१',
    },
    {
      'type': 'Festival Leave (पर्व विदा)',
      'startDate': '२०८१ कार्तिक ०५',
      'endDate': '२०८१ कार्तिक ०६',
      'days': 2,
      'reason': 'Travel to home district for festival celebration',
      'substitute': 'Pending substitution assign',
      'status': 'Pending Approval',
      'appliedOn': '२०८१ असोज ०८',
    },
  ];

  void _showApplyModal(bool isDark) {
    String leaveType = 'Casual Leave (आकस्मिक विदा)';
    final reasonCtrl = TextEditingController();
    String substituteTeacher = 'Mr. Ramesh Bhattarai (Maths)';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (modalCtx, setModalState) => Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            20,
            20,
            MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.event_available_rounded, color: AppTheme.purple, size: 22),
                        SizedBox(width: 8),
                        Text(
                          'Apply Leave (विदा निवेदन)',
                          style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, size: 20),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Leave Type
                DropdownButtonFormField<String>(
                  value: leaveType,
                  decoration: InputDecoration(
                    labelText: 'Leave Category / विदाको प्रकार',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  items: [
                    'Casual Leave (आकस्मिक विदा)',
                    'Sick Leave (बिरामी विदा)',
                    'Festival Leave (पर्व विदा)',
                    'Special Academic Leave',
                  ]
                      .map((t) => DropdownMenuItem(value: t, child: Text(t, style: const TextStyle(fontSize: 13))))
                      .toList(),
                  onChanged: (val) => setModalState(() => leaveType = val!),
                ),
                const SizedBox(height: 12),

                // Date Fields
                Row(
                  children: [
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('From Date (शुरु मिति)', style: TextStyle(fontSize: 11, color: AppTheme.darkSubtext)),
                            SizedBox(height: 2),
                            Text('२०८१ असोज १२', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('To Date (अन्तिम मिति)', style: TextStyle(fontSize: 11, color: AppTheme.darkSubtext)),
                            SizedBox(height: 2),
                            Text('२०८१ असोज १३ (2 Days)', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Substitute Teacher
                DropdownButtonFormField<String>(
                  value: substituteTeacher,
                  decoration: InputDecoration(
                    labelText: 'Period Substitute Teacher / सट्टा शिक्षक',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  items: [
                    'Mr. Ramesh Bhattarai (Maths)',
                    'Mrs. Gita Sharma (Science)',
                    'Mr. Bimal Adhikari (English)',
                  ]
                      .map((t) => DropdownMenuItem(value: t, child: Text(t, style: const TextStyle(fontSize: 13))))
                      .toList(),
                  onChanged: (val) => setModalState(() => substituteTeacher = val!),
                ),
                const SizedBox(height: 12),

                // Reason
                TextField(
                  controller: reasonCtrl,
                  maxLines: 3,
                  decoration: InputDecoration(
                    labelText: 'Reason for Leave / कारण',
                    hintText: 'Brief explanation for Principal / Vice Principal...',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
                const SizedBox(height: 18),

                // Submit Button
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.purple,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    onPressed: () {
                      if (reasonCtrl.text.trim().isEmpty) return;
                      Navigator.pop(ctx);
                      setState(() {
                        _myLeaves.insert(0, {
                          'type': leaveType,
                          'startDate': '२०८१ असोज १२',
                          'endDate': '२०८१ असोज १३',
                          'days': 2,
                          'reason': reasonCtrl.text.trim(),
                          'substitute': substituteTeacher,
                          'status': 'Pending Approval',
                          'appliedOn': '२०८१ असोज १० (Today)',
                        });
                      });
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          backgroundColor: AppTheme.green,
                          content: Text('Leave application dispatched to Principal Office ✅'),
                        ),
                      );
                    },
                    child: const Text('Submit Application (निवेदन पेश गर्नुहोस्)',
                        style: TextStyle(fontWeight: FontWeight.w800)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
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
              'Leave Management (बिदा अभिलेख)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Staff Leave Balance & Approval Status',
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
          Container(
            margin: const EdgeInsets.only(right: 14),
            child: ElevatedButton.icon(
              onPressed: () => _showApplyModal(isDark),
              icon: const Icon(Icons.add_rounded, size: 18),
              label: const Text(
                'Apply',
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
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(18, 6, 18, 90),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Leave Balance Strip
            Row(
              children: [
                _LeaveBalanceBox(label: 'Casual (आकस्मिक)', remaining: '6', total: '12', color: AppTheme.primary, isDark: isDark),
                const SizedBox(width: 8),
                _LeaveBalanceBox(label: 'Sick (बिरामी)', remaining: '8', total: '12', color: AppTheme.green, isDark: isDark),
                const SizedBox(width: 8),
                _LeaveBalanceBox(label: 'Festival (पर्व)', remaining: '14', total: '20', color: AppTheme.orange, isDark: isDark),
              ],
            ),
            const SizedBox(height: 20),

            // History Header
            const SectionHeader(
              title: 'Leave Applications & Requests (निवेदन इतिहास)',
              subtitle: 'Approval timeline and substitute teachers',
            ),
            const SizedBox(height: 10),

            ..._myLeaves.asMap().entries.map((e) {
              final leave = e.value;
              final isApproved = leave['status'] == 'Approved';
              final statusColor = isApproved ? AppTheme.green : AppTheme.orange;

              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(16),
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
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: statusColor.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            isApproved ? Icons.check_circle_rounded : Icons.hourglass_top_rounded,
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
                                leave['type'] as String,
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${leave['days']} Days (${leave['startDate']} to ${leave['endDate']})',
                                style: const TextStyle(color: AppTheme.darkSubtext, fontSize: 11.5),
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
                            leave['status'] as String,
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
                    Text(
                      'Reason: ${leave['reason']}',
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? Colors.white70 : AppTheme.dark,
                        height: 1.35,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(Icons.swap_horiz_rounded, size: 14, color: AppTheme.primary),
                        const SizedBox(width: 4),
                        Text(
                          'Substitute: ${leave['substitute']}',
                          style: const TextStyle(fontSize: 11, color: AppTheme.primary, fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Applied: ${leave['appliedOn']}',
                      style: const TextStyle(fontSize: 10.5, color: AppTheme.darkSubtext),
                    ),
                  ],
                ),
              ).animate().fadeIn(delay: (e.key * 60).ms);
            }),
          ],
        ),
      ),
    );
  }
}

class _LeaveBalanceBox extends StatelessWidget {
  final String label, remaining, total;
  final Color color;
  final bool isDark;

  const _LeaveBalanceBox({
    required this.label,
    required this.remaining,
    required this.total,
    required this.color,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
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
              color: Colors.black.withOpacity(isDark ? 0.2 : 0.03),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  remaining,
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: color),
                ),
                Text(
                  '/$total',
                  style: const TextStyle(fontSize: 12, color: AppTheme.darkSubtext, fontWeight: FontWeight.w700),
                ),
              ],
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: isDark ? Colors.white70 : AppTheme.dark),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
