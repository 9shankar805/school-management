import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

import 'grades_screen.dart';
import 'attendance_screen.dart';
import 'assignments_screen.dart';
import 'exams_screen.dart';
import 'schedule_screen.dart';
import 'health_screen.dart';
import 'behavior_screen.dart';
import 'report_card_screen.dart';

class ChildrenScreen extends StatefulWidget {
  const ChildrenScreen({super.key});
  @override
  State<ChildrenScreen> createState() => _ChildrenScreenState();
}

class _ChildrenScreenState extends State<ChildrenScreen> {
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final api = context.watch<ApiProvider>();
    final children = api.children;
    final child = api.selectedChild;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Student Profile'),
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.badge_outlined),
            tooltip: 'Digital Student ID',
            onPressed: () => _showDigitalIdCard(context, child),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Multi-child switcher
            if (children.length > 1) ...[
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: children.map((c) {
                    final isSelected = c.id == child.id;
                    return GestureDetector(
                      onTap: () => api.selectChild(c),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        margin: const EdgeInsets.only(right: 10),
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                        decoration: BoxDecoration(
                          gradient: isSelected ? AppTheme.primaryGradient : null,
                          color: isSelected ? null : (isDark ? AppTheme.darkCard : Colors.white),
                          borderRadius: BorderRadius.circular(24),
                          border: Border.all(
                            color: isSelected ? Colors.transparent : (isDark ? AppTheme.darkBorder : const Color(0xFFE5E9F0)),
                            width: 1,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: isSelected ? AppTheme.primary.withOpacity(0.3) : Colors.black.withOpacity(0.04),
                              blurRadius: 8,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Text(
                          c.name.split(' ').first,
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                            color: isSelected ? Colors.white : (isDark ? Colors.white70 : AppTheme.dark),
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Student Hero Info Card
            GradientCard(
              colors: const [Color(0xFF6C3CE9), Color(0xFF8E24AA)],
              child: Column(
                children: [
                  Row(
                    children: [
                      Container(
                        width: 72,
                        height: 72,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white24,
                          border: Border.all(color: Colors.white, width: 2.5),
                        ),
                        child: Center(
                          child: Text(
                            child.avatar,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 28,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              child.name,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 20,
                                fontWeight: FontWeight.w900,
                                letterSpacing: -0.3,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              '${child.grade} • ${child.section}',
                              style: const TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w600),
                            ),
                            Text(
                              'Roll No: ${child.rollNo} • Adm: ${child.admissionNo}',
                              style: const TextStyle(color: Colors.white60, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.black.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _DetailBadge(icon: Icons.bloodtype_outlined, text: child.bloodGroup),
                        _DetailBadge(icon: Icons.cake_outlined, text: child.birthday),
                        _DetailBadge(icon: Icons.wc_outlined, text: child.gender),
                      ],
                    ),
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05),
            const SizedBox(height: 16),

            // Stat Metrics
            Row(
              children: [
                MetricStatCard(
                  label: 'GPA Score',
                  value: child.gpa.toStringAsFixed(2),
                  icon: Icons.star_rounded,
                  color: AppTheme.amber,
                  subtitle: 'Top 5% in class',
                ),
                const SizedBox(width: 10),
                MetricStatCard(
                  label: 'Attendance',
                  value: '${(child.attendance * 100).toInt()}%',
                  icon: Icons.how_to_reg_rounded,
                  color: AppTheme.green,
                  subtitle: 'Regular',
                ),
                const SizedBox(width: 10),
                MetricStatCard(
                  label: 'Class Rank',
                  value: '#${child.rank}',
                  icon: Icons.emoji_events_rounded,
                  color: AppTheme.primary,
                  subtitle: 'Out of 42',
                ),
              ],
            ).animate().fadeIn(delay: 100.ms),
            const SizedBox(height: 24),

            // Academic modules quick tiles
            const SectionHeader(title: '📚 Academic & Performance Services'),
            const SizedBox(height: 12),
            _AcademicTile(
              icon: Icons.bar_chart_rounded,
              label: 'Grades & Terminal Marks',
              subtitle: 'Current GPA: ${child.gpa.toStringAsFixed(2)} • Top 5% standing',
              color: AppTheme.blue,
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GradesScreen())),
            ).animate().fadeIn(delay: 120.ms),
            _AcademicTile(
              icon: Icons.description_outlined,
              label: 'Terminal Report Card',
              subtitle: 'Official digital marksheet & teacher remarks',
              color: AppTheme.purple,
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ReportCardScreen())),
            ).animate().fadeIn(delay: 160.ms),
            _AcademicTile(
              icon: Icons.how_to_reg_rounded,
              label: 'Attendance Log',
              subtitle: '${(child.attendance * 100).toInt()}% total attendance across subjects',
              color: AppTheme.green,
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AttendanceScreen())),
            ).animate().fadeIn(delay: 200.ms),
            _AcademicTile(
              icon: Icons.assignment_outlined,
              label: 'Assignments & Homework',
              subtitle: '${api.assignments.where((a) => !a.isCompleted).length} tasks pending submission',
              color: AppTheme.primary,
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AssignmentsScreen())),
            ).animate().fadeIn(delay: 240.ms),
            _AcademicTile(
              icon: Icons.calendar_month_rounded,
              label: 'Weekly Class Routine',
              subtitle: 'Period timings, room numbers & faculty schedule',
              color: AppTheme.teal,
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ScheduleScreen())),
            ).animate().fadeIn(delay: 280.ms),
            _AcademicTile(
              icon: Icons.health_and_safety_outlined,
              label: 'Health & Immunization Record',
              subtitle: 'Medical profile, allergies & nurse clinic visits',
              color: AppTheme.green,
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HealthScreen())),
            ).animate().fadeIn(delay: 320.ms),
            _AcademicTile(
              icon: Icons.emoji_events_outlined,
              label: 'Behavior & Merits',
              subtitle: 'Merit points, conduct timeline & commendations',
              color: AppTheme.orange,
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BehaviorScreen())),
            ).animate().fadeIn(delay: 360.ms),
            const SizedBox(height: 24),

            // Subject Grade list
            SectionHeader(
              title: '📖 Subject Performance',
              actionLabel: 'Details',
              onAction: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GradesScreen())),
            ),
            const SizedBox(height: 12),
            ...api.subjects.map((sub) => _SubjectRow(subject: sub)),
          ],
        ),
      ),
    );
  }

  void _showDigitalIdCard(BuildContext context, Child child) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        contentPadding: const EdgeInsets.all(20),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: AppTheme.primaryGradient,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      const Icon(Icons.school, color: Colors.white, size: 24),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          child.schoolName,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                  const Divider(color: Colors.white24, height: 24),
                  Row(
                    children: [
                      Container(
                        width: 60,
                        height: 60,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white24,
                          border: Border.all(color: Colors.white, width: 2),
                        ),
                        child: Center(
                          child: Text(
                            child.avatar,
                            style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.w900),
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              child.name,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 16),
                            ),
                            Text(
                              '${child.grade} • ${child.section}',
                              style: const TextStyle(color: Colors.white70, fontSize: 12),
                            ),
                            Text(
                              'Roll: ${child.rollNo} • Adm: ${child.admissionNo}',
                              style: const TextStyle(color: Colors.white60, fontSize: 10),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.black.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        Text('Blood: ${child.bloodGroup}', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                        Text('Valid: 2081-2082', style: const TextStyle(color: Colors.white70, fontSize: 11)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            const Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.qr_code_2_rounded, size: 48, color: AppTheme.primary),
                SizedBox(width: 10),
                Text('Verified Student ID\nNepal School Hub', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              ],
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Close')),
        ],
      ),
    );
  }
}

class _DetailBadge extends StatelessWidget {
  final IconData icon;
  final String text;
  const _DetailBadge({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, color: Colors.white70, size: 14),
        const SizedBox(width: 4),
        Text(text, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
      ],
    );
  }
}

class _AcademicTile extends StatelessWidget {
  final IconData icon;
  final String label, subtitle;
  final Color color;
  final VoidCallback onTap;

  const _AcademicTile({
    required this.icon,
    required this.label,
    required this.subtitle,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      child: AppCard(
        padding: const EdgeInsets.all(14),
        onTap: onTap,
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: color, size: 22),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                  ),
                ],
              ),
            ),
            const Icon(Icons.chevron_right, color: AppTheme.grey, size: 20),
          ],
        ),
      ),
    );
  }
}

class _SubjectRow extends StatelessWidget {
  final SubjectGrade subject;
  const _SubjectRow({required this.subject});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      child: AppCard(
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: Color(subject.colorHex).withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(subjectIcon(subject.iconCode), size: 22, color: Color(subject.colorHex)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(subject.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                      GradeBadge(grade: subject.grade),
                    ],
                  ),
                  const SizedBox(height: 6),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: LinearProgressIndicator(
                      value: subject.progress,
                      backgroundColor: AppTheme.greyLight,
                      valueColor: AlwaysStoppedAnimation<Color>(Color(subject.colorHex)),
                      minHeight: 6,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(subject.teacher, style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                      Text('${subject.score.toInt()}%', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(subject.colorHex))),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
