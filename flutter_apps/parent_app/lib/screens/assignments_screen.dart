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
          onToggleStatus: () async {
            final newStatus = a.isCompleted ? 'pending' : 'completed';
            await ApiService.updateAssignmentStatus(a.id ?? 1, newStatus);
            if (context.mounted) {
              context.read<ApiProvider>().loadChildSpecificData();
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(a.isCompleted ? 'Marked as pending' : 'Assignment marked completed!'),
                  duration: const Duration(seconds: 2),
                ),
              );
            }
          },
        ).animate().fadeIn(delay: (idx * 50).ms).slideY(begin: 0.05);
      },
    );
  }
}

class _AssignmentCard extends StatelessWidget {
  final Assignment assignment;
  final VoidCallback onToggleStatus;

  const _AssignmentCard({required this.assignment, required this.onToggleStatus});

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
        onTap: () => _showDetailSheet(context),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Color(assignment.colorHex).withOpacity(0.12),
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

  void _showDetailSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(assignment.subject, style: const TextStyle(fontSize: 12, color: AppTheme.primary, fontWeight: FontWeight.w800)),
                StatusBadge(label: assignment.isCompleted ? 'Submitted' : 'Pending', color: assignment.isCompleted ? AppTheme.green : AppTheme.orange),
              ],
            ),
            const SizedBox(height: 8),
            Text(assignment.title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            const SizedBox(height: 12),
            const Text('Instructions & Questions:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
            const SizedBox(height: 4),
            Text(assignment.description, style: const TextStyle(fontSize: 13, color: AppTheme.dark)),
            const SizedBox(height: 16),
            Row(
              children: [
                const Icon(Icons.calendar_today_outlined, size: 16, color: AppTheme.grey),
                const SizedBox(width: 8),
                Text('Due Date: ${assignment.dueDate}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              ],
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  onToggleStatus();
                },
                child: Text(assignment.isCompleted ? 'Re-open Assignment' : 'Mark as Completed'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
