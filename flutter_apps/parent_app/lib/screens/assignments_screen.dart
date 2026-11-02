import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../services/api_service.dart';
import '../widgets/common_widgets.dart';

class AssignmentsScreen extends StatefulWidget {
  const AssignmentsScreen({super.key});
  @override
  State<AssignmentsScreen> createState() => _AssignmentsScreenState();
}

class _AssignmentsScreenState extends State<AssignmentsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final pending = api.assignments.where((a) => !a.isCompleted).toList();
    final completed = api.assignments.where((a) => a.isCompleted).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Homework & Assignments'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        bottom: TabBar(
          controller: _tabCtrl,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.grey,
          indicatorColor: AppTheme.primary,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: [
            Tab(text: 'Pending (${pending.length})'),
            Tab(text: 'Completed (${completed.length})'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabCtrl,
        children: [
          _buildAssignmentList(pending, isPending: true),
          _buildAssignmentList(completed, isPending: false),
        ],
      ),
    );
  }

  Widget _buildAssignmentList(List<Assignment> list, {required bool isPending}) {
    if (list.isEmpty) {
      return EmptyStateWidget(
        icon: isPending ? Icons.check_circle_outline : Icons.assignment_outlined,
        title: isPending ? 'All caught up! 🎉' : 'No completed homework yet',
        subtitle: isPending ? 'No pending assignments for this term.' : 'Completed assignments will appear here.',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 40),
      itemCount: list.length,
      itemBuilder: (context, idx) {
        final a = list[idx];
        return _AssignmentCard(
          assignment: a,
          onToggleStatus: () => _toggleAssignment(a),
          onTapDetail: () => _showDetailSheet(context, a),
        ).animate().fadeIn(delay: (idx * 50).ms).slideY(begin: 0.05);
      },
    );
  }

  Future<void> _toggleAssignment(Assignment a) async {
    final newStatus = a.isCompleted ? 'pending' : 'completed';
    await ApiService.updateAssignmentStatus(a.id ?? 1, newStatus);
    if (mounted) {
      context.read<ApiProvider>().loadChildSpecificData();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(a.isCompleted ? 'Marked assignment as pending' : 'Assignment marked as completed!'),
          backgroundColor: a.isCompleted ? AppTheme.primary : const Color(0xFF047857),
          duration: const Duration(seconds: 2),
        ),
      );
    }
  }

  void _showDetailSheet(BuildContext context, Assignment a) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(24, 20, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Color(a.colorHex).withValues(alpha: 0.14),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    a.subject,
                    style: TextStyle(fontSize: 12, color: Color(a.colorHex), fontWeight: FontWeight.w800),
                  ),
                ),
                StatusBadge(
                  label: a.isCompleted ? 'SUBMITTED' : 'DUE SOON',
                  color: a.isCompleted ? AppTheme.green : AppTheme.orange,
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(a.title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            const SizedBox(height: 14),

            // Instructions Box
            const Text('Assignment Instructions:', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Text(
                a.description,
                style: const TextStyle(fontSize: 13, color: Color(0xFF0F172A), height: 1.35),
              ),
            ),
            const SizedBox(height: 14),

            // Meta Details
            Row(
              children: [
                const Icon(Icons.calendar_today_outlined, size: 16, color: AppTheme.grey),
                const SizedBox(width: 8),
                Text('Submission Due Date: ${a.dueDate}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              ],
            ),
            const SizedBox(height: 12),

            // Attached Worksheet Simulation
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.picture_as_pdf, color: Color(0xFFDC2626), size: 24),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Reference_Worksheet.pdf', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                        Text('1.4 MB • Official Lesson Material', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.download, size: 20, color: AppTheme.primary),
                    tooltip: 'Download Worksheet',
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Downloading Reference_Worksheet.pdf...')),
                      );
                    },
                  ),
                ],
              ),
            ),

            if (a.isCompleted) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFBBF7D0)),
                ),
                child: const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Teacher Evaluation: 9.5 / 10 (A+)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: Color(0xFF166534))),
                    SizedBox(height: 2),
                    Text('"Exemplary accuracy and neat problem-solving presentation."', style: TextStyle(fontSize: 11, fontStyle: FontStyle.italic, color: Color(0xFF14532D))),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 20),
            Row(
              children: [
                if (!a.isCompleted)
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Selected solution photo from camera/gallery!')),
                        );
                      },
                      icon: const Icon(Icons.attach_file, size: 16),
                      label: const Text('Attach File'),
                    ),
                  ),
                if (!a.isCompleted) const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(ctx);
                      _toggleAssignment(a);
                    },
                    child: Text(a.isCompleted ? 'Re-open Assignment' : 'Submit as Completed'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _AssignmentCard extends StatelessWidget {
  final Assignment assignment;
  final VoidCallback onToggleStatus;
  final VoidCallback onTapDetail;

  const _AssignmentCard({
    required this.assignment,
    required this.onToggleStatus,
    required this.onTapDetail,
  });

  Color get _priorityColor {
    switch (assignment.priority.toLowerCase()) {
      case 'high':
        return AppTheme.red;
      case 'medium':
        return AppTheme.orange;
      default:
        return AppTheme.green;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: AppCard(
        padding: const EdgeInsets.all(16),
        onTap: onTapDetail,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Color(assignment.colorHex).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    assignment.subject,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: Color(assignment.colorHex),
                    ),
                  ),
                ),
                StatusBadge(
                  label: assignment.priority.toUpperCase(),
                  color: _priorityColor,
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              assignment.title,
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 4),
            Text(
              assignment.description,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 12, color: AppTheme.grey),
            ),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.access_time, size: 14, color: AppTheme.grey),
                    const SizedBox(width: 4),
                    Text(
                      'Due: ${assignment.dueDate}',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.grey),
                    ),
                  ],
                ),
                GestureDetector(
                  onTap: onToggleStatus,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: assignment.isCompleted ? AppTheme.greenLight : AppTheme.primaryLight,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          assignment.isCompleted ? Icons.check_circle : Icons.circle_outlined,
                          size: 14,
                          color: assignment.isCompleted ? AppTheme.green : AppTheme.primary,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          assignment.isCompleted ? 'Completed' : 'Mark Done',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: assignment.isCompleted ? AppTheme.green : AppTheme.primary,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
