import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

import 'notifications_screen.dart';
import 'bus_tracking_screen.dart';
import 'grades_screen.dart';
import 'attendance_screen.dart';
import 'exams_screen.dart';
import 'assignments_screen.dart';
import 'report_card_screen.dart';
import 'teacher_contact_screen.dart';
import 'meeting_screen.dart';
import 'events_screen.dart';
import 'health_screen.dart';
import 'behavior_screen.dart';
import 'leave_request_screen.dart';
import 'schedule_screen.dart';
import 'fees_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final unread = api.unreadNotificationsCount;

    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () => api.loadAllData(),
          color: AppTheme.primary,
          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            slivers: [
              if (api.isLoading)
                const SliverToBoxAdapter(child: AppLinearLoader()),
              SliverToBoxAdapter(
                child: _buildHeader(context, api, isDark, unread)
                    .animate()
                    .fadeIn(duration: 400.ms),
              ),
              SliverToBoxAdapter(
                child: _buildChildSelector(api, isDark)
                    .animate()
                    .fadeIn(delay: 80.ms),
              ),
              SliverToBoxAdapter(
                child: _buildChildHeroCard(child, api)
                    .animate()
                    .fadeIn(delay: 150.ms)
                    .slideY(begin: 0.05),
              ),
              SliverToBoxAdapter(
                child: _buildBusLiveTicker(context, api)
                    .animate()
                    .fadeIn(delay: 200.ms),
              ),
              SliverToBoxAdapter(
                child: _buildQuickAccessGrid(context)
                    .animate()
                    .fadeIn(delay: 250.ms),
              ),
              SliverToBoxAdapter(
                child: _buildTodaySchedule(context)
                    .animate()
                    .fadeIn(delay: 300.ms),
              ),
              SliverToBoxAdapter(
                child: _buildUpcomingExamCard(context, api)
                    .animate()
                    .fadeIn(delay: 350.ms),
              ),
              SliverToBoxAdapter(
                child: _buildRecentAlerts(context, api)
                    .animate()
                    .fadeIn(delay: 400.ms),
              ),
              SliverToBoxAdapter(
                child: _buildComprehensiveServices(context)
                    .animate()
                    .fadeIn(delay: 450.ms),
              ),
              const SliverToBoxAdapter(child: SizedBox(height: 90)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context, ApiProvider api, bool isDark, int unread) {
    final profile = api.userProfile;
    final parentName = profile?['name'] ?? profile?['full_name'] ?? 'Parent Account';
    final initials = parentName.isNotEmpty ? parentName[0].toUpperCase() : 'P';

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              gradient: AppTheme.primaryGradient,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: AppTheme.primary.withOpacity(0.3),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Center(
              child: Text(
                initials,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Text('Namaste! 🇳🇵', style: TextStyle(fontSize: 12, color: AppTheme.grey, fontWeight: FontWeight.w600)),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppTheme.green.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.check_circle, color: AppTheme.green, size: 10),
                          SizedBox(width: 3),
                          Text('Online', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: AppTheme.green)),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  parentName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.3,
                    color: isDark ? Colors.white : AppTheme.dark,
                  ),
                ),
              ],
            ),
          ),
          CircleIconButton(
            icon: Icons.notifications_outlined,
            badge: unread,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildChildSelector(ApiProvider api, bool isDark) {
    if (api.children.length <= 1) return const SizedBox.shrink();

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Row(
        children: api.children.map((child) {
          final isSelected = child.id == api.selectedChild.id;
          return GestureDetector(
            onTap: () => api.selectChild(child),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              margin: const EdgeInsets.only(right: 10),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
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
                    color: isSelected ? AppTheme.primary.withOpacity(0.25) : Colors.black.withOpacity(0.03),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: isSelected ? Colors.white24 : AppTheme.primary.withOpacity(0.12),
                    ),
                    child: Center(
                      child: Text(
                        child.avatar,
                        style: TextStyle(
                          fontWeight: FontWeight.w900,
                          fontSize: 11,
                          color: isSelected ? Colors.white : AppTheme.primary,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    child.firstName.isNotEmpty ? child.firstName : child.name.split(' ').first,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: isSelected ? Colors.white : (isDark ? Colors.white70 : AppTheme.dark),
                    ),
                  ),
                ],
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildChildHeroCard(Child child, ApiProvider api) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: GradientCard(
        colors: const [Color(0xFF6C3CE9), Color(0xFF8E24AA)],
        child: Column(
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          child.schoolName,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        child.name,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 22,
                          fontWeight: FontWeight.w900,
                          letterSpacing: -0.3,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${child.grade} • ${child.section} • Roll #${child.rollNo}',
                        style: const TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white24,
                    border: Border.all(color: Colors.white, width: 2.5),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.15),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Center(
                    child: Text(
                      child.avatar,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 26,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.black.withOpacity(0.15),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _HeroStat(label: 'Overall GPA', value: child.gpa.toStringAsFixed(2), icon: Icons.star_rounded),
                  Container(width: 1, height: 28, color: Colors.white24),
                  _HeroStat(
                    label: 'Attendance',
                    value: '${(child.attendance * 100).toInt()}%',
                    icon: Icons.how_to_reg_rounded,
                  ),
                  Container(width: 1, height: 28, color: Colors.white24),
                  _HeroStat(label: 'Class Rank', value: '#${child.rank}', icon: Icons.emoji_events_rounded),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBusLiveTicker(BuildContext context, ApiProvider api) {
    final bus = api.busInfo;

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
      child: AppCard(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const BusTrackingScreen()),
        ),
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: AppTheme.teal.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Icon(Icons.directions_bus_rounded, color: AppTheme.teal, size: 26),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 8,
                        height: 8,
                        decoration: const BoxDecoration(
                          color: AppTheme.green,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        bus.busNumber,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
                      ),
                      const Spacer(),
                      StatusBadge(label: 'ETA ${bus.eta}', color: AppTheme.teal),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Text(
                    'Next: ${bus.nextStop}',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            const Icon(Icons.chevron_right, color: AppTheme.grey, size: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickAccessGrid(BuildContext context) {
    final actions = [
      {'label': 'Grades', 'icon': Icons.bar_chart_rounded, 'color': AppTheme.blue, 'screen': const GradesScreen()},
      {'label': 'Attendance', 'icon': Icons.how_to_reg_rounded, 'color': AppTheme.green, 'screen': const AttendanceScreen()},
      {'label': 'Homework', 'icon': Icons.assignment_outlined, 'color': AppTheme.primary, 'screen': const AssignmentsScreen()},
      {'label': 'Exams', 'icon': Icons.quiz_rounded, 'color': AppTheme.orange, 'screen': const ExamsScreen()},
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SectionHeader(title: '⚡ Quick Access'),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: actions.map((a) {
              final color = a['color'] as Color;
              return GestureDetector(
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => a['screen'] as Widget),
                ),
                child: Column(
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: color.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: color.withOpacity(0.2), width: 1),
                      ),
                      child: Icon(a['icon'] as IconData, color: color, size: 28),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      a['label'] as String,
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildTodaySchedule(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SectionHeader(
            title: "📅 Today's Routine",
            actionLabel: 'Full Timetable',
            onAction: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ScheduleScreen()),
            ),
          ),
          const SizedBox(height: 12),
          ...AppData.schedule.take(3).map((item) {
            return Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: Color(item['colorHex'] as int),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      item['time'] as String,
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppTheme.primaryDark),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item['subject'] as String,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppTheme.dark),
                        ),
                        Text(
                          '${item['teacher']} • ${item['room']}',
                          style: TextStyle(fontSize: 11, color: AppTheme.dark.withOpacity(0.6), fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),
                  Text(
                    item['duration'] as String,
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.dark.withOpacity(0.5)),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildUpcomingExamCard(BuildContext context, ApiProvider api) {
    final exam = api.exams.isNotEmpty ? api.exams.first : null;

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SectionHeader(
            title: '📝 Upcoming Examination',
            actionLabel: 'Exam Schedule',
            onAction: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ExamsScreen()),
            ),
          ),
          const SizedBox(height: 12),
          AppCard(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ExamsScreen()),
            ),
            child: exam == null
                ? const Text('No upcoming exams scheduled.')
                : Row(
                    children: [
                      Container(
                        width: 52,
                        height: 52,
                        decoration: BoxDecoration(
                          color: AppTheme.orange.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Icon(Icons.quiz_rounded, color: AppTheme.orange, size: 26),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              exam.subject,
                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              '${exam.type} • ${exam.date}',
                              style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontWeight: FontWeight.w600),
                            ),
                            Text(
                              '${exam.time} • ${exam.room}',
                              style: const TextStyle(fontSize: 11, color: AppTheme.primary, fontWeight: FontWeight.w700),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppTheme.orange.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Column(
                          children: [
                            Text(
                              '${exam.daysLeft}',
                              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppTheme.orange),
                            ),
                            const Text('days left', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: AppTheme.orange)),
                          ],
                        ),
                      ),
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecentAlerts(BuildContext context, ApiProvider api) {
    final recent = api.notifications.take(3).toList();

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SectionHeader(
            title: '🔔 School Alerts & Notices',
            actionLabel: 'See All',
            onAction: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
            ),
          ),
          const SizedBox(height: 12),
          if (recent.isEmpty)
            const AppCard(child: Center(child: Text('No active alerts.')))
          else
            ...recent.map((n) {
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withOpacity(0.04),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppTheme.primary.withOpacity(0.1)),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: AppTheme.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.campaign_rounded, color: AppTheme.primary, size: 18),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(n.title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                          Text(
                            n.body,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                          ),
                        ],
                      ),
                    ),
                    Text(n.time, style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildComprehensiveServices(BuildContext context) {
    final services = [
      {'label': 'Report Card', 'icon': Icons.description_outlined, 'color': AppTheme.purple, 'screen': const ReportCardScreen()},
      {'label': 'Teachers', 'icon': Icons.people_outline_rounded, 'color': AppTheme.blue, 'screen': const TeacherContactScreen()},
      {'label': 'Meetings', 'icon': Icons.event_outlined, 'color': AppTheme.orange, 'screen': const MeetingScreen()},
      {'label': 'Events', 'icon': Icons.celebration_outlined, 'color': AppTheme.teal, 'screen': const EventsScreen()},
      {'label': 'Health', 'icon': Icons.health_and_safety_outlined, 'color': AppTheme.green, 'screen': const HealthScreen()},
      {'label': 'Behavior', 'icon': Icons.emoji_events_outlined, 'color': AppTheme.primary, 'screen': const BehaviorScreen()},
      {'label': 'Leave App', 'icon': Icons.event_busy_outlined, 'color': AppTheme.red, 'screen': const LeaveRequestScreen()},
      {'label': 'Fee Ledger', 'icon': Icons.receipt_long_rounded, 'color': AppTheme.amber, 'screen': const FeesScreen()},
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SectionHeader(title: '🏫 School Hub Modules'),
          const SizedBox(height: 12),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              crossAxisSpacing: 10,
              mainAxisSpacing: 12,
              childAspectRatio: 0.82,
            ),
            itemCount: services.length,
            itemBuilder: (_, i) {
              final s = services[i];
              final color = s['color'] as Color;
              return GestureDetector(
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => s['screen'] as Widget),
                ),
                child: Column(
                  children: [
                    Container(
                      width: 54,
                      height: 54,
                      decoration: BoxDecoration(
                        color: color.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: color.withOpacity(0.2), width: 1),
                      ),
                      child: Icon(s['icon'] as IconData, color: color, size: 24),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      s['label'] as String,
                      textAlign: TextAlign.center,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}

class _HeroStat extends StatelessWidget {
  final String label, value;
  final IconData icon;
  const _HeroStat({required this.label, required this.value, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: Colors.white70),
            const SizedBox(width: 4),
            Text(
              value,
              style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
            ),
          ],
        ),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.w600)),
      ],
    );
  }
}
