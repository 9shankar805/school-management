import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../services/api_service.dart';
import '../widgets/common_widgets.dart';

class LeaveRequestScreen extends StatefulWidget {
  const LeaveRequestScreen({super.key});
  @override
  State<LeaveRequestScreen> createState() => _LeaveRequestScreenState();
}

class _LeaveRequestScreenState extends State<LeaveRequestScreen> {
  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final leaves = api.leaveRequests;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Leave Applications'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // New Leave Application Hero Action Card
            AppCard(
              padding: const EdgeInsets.all(16),
              color: AppTheme.primaryLight,
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: const BoxDecoration(
                      color: AppTheme.primary,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.edit_calendar_rounded, color: Colors.white, size: 24),
                  ),
                  const SizedBox(width: 14),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Apply for Student Leave', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: AppTheme.primaryDark)),
                        Text('Submit medical or urgent absence notice', style: TextStyle(fontSize: 11, color: AppTheme.primaryDark)),
                      ],
                    ),
                  ),
                  ElevatedButton(
                    onPressed: () => _showApplyLeaveSheet(context),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    ),
                    child: const Text('Apply', style: TextStyle(fontSize: 12)),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            const SectionHeader(title: '📋 Leave Application History'),
            const SizedBox(height: 12),
            if (leaves.isEmpty)
              const EmptyStateWidget(
                icon: Icons.event_busy_outlined,
                title: 'No leave applications submitted',
                subtitle: 'Tap the Apply button above to submit an absence request.',
              )
            else
              ...leaves.map((l) => _LeaveCard(leave: l)),
          ],
        ),
      ),
    );
  }

  void _showApplyLeaveSheet(BuildContext context) {
    String selectedType = 'Sick / Medical Leave';
    String startDate = '2026-10-05';
    String endDate = '2026-10-06';
    final reasonCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(24, 24, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Student Leave Request', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 14),
              const Text('Leave Type / Category:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(
                  color: AppTheme.lightBg,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: selectedType,
                    isExpanded: true,
                    items: const [
                      DropdownMenuItem(value: 'Sick / Medical Leave', child: Text('Sick / Medical Leave')),
                      DropdownMenuItem(value: 'Urgent Family Function', child: Text('Urgent Family Function')),
                      DropdownMenuItem(value: 'Religious & Festival Leave', child: Text('Religious & Festival Leave')),
                      DropdownMenuItem(value: 'Other Reason', child: Text('Other Reason')),
                    ],
                    onChanged: (val) {
                      if (val != null) setSheetState(() => selectedType = val);
                    },
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: AppTheme.lightBg, borderRadius: BorderRadius.circular(14)),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Start Date', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                          Text(startDate, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: AppTheme.lightBg, borderRadius: BorderRadius.circular(14)),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('End Date', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                          Text(endDate, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: reasonCtrl,
                maxLines: 3,
                decoration: InputDecoration(
                  labelText: 'Reason for absence & Doctor remarks',
                  filled: true,
                  fillColor: AppTheme.lightBg,
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: () async {
                    Navigator.pop(ctx);
                    await ApiService.submitLeaveRequest({
                      'leave_type': selectedType,
                      'start_date': startDate,
                      'end_date': endDate,
                      'reason': reasonCtrl.text.isNotEmpty ? reasonCtrl.text : 'Medical consultation rest.',
                    });
                    if (context.mounted) {
                      context.read<ApiProvider>().loadChildSpecificData();
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Leave application submitted to School Principal & Class Teacher!'),
                          backgroundColor: AppTheme.green,
                        ),
                      );
                    }
                  },
                  child: const Text('Submit Application'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _LeaveCard extends StatelessWidget {
  final LeaveRequestItem leave;
  const _LeaveCard({required this.leave});

  @override
  Widget build(BuildContext context) {
    final isApproved = leave.status.toLowerCase() == 'approved';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: AppCard(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryLight,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    leave.leaveType,
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppTheme.primary),
                  ),
                ),
                StatusBadge(
                  label: isApproved ? 'APPROVED' : 'UNDER REVIEW',
                  color: isApproved ? AppTheme.green : AppTheme.orange,
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(leave.reason, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
            const SizedBox(height: 4),
            Text('Duration: ${leave.startDate} to ${leave.endDate} (${leave.totalDays} Days)', style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontWeight: FontWeight.w600)),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Sanctioned By: ${leave.approvedBy}', style: const TextStyle(fontSize: 10, color: AppTheme.grey, fontWeight: FontWeight.w600)),
                Text('Applied: ${leave.appliedDate}', style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
