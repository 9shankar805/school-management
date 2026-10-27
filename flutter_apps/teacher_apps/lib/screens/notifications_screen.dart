import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<AppNotification> _notifications = [];
  int _selectedFilter = 0; // 0=All, 1=Unread, 2=Urgent
  bool _initialized = false;

  final List<String> _filters = ['All Alerts', 'Unread', 'Substitution & Exams'];

  void _initNotifications(List<dynamic> raw) {
    if (raw.isNotEmpty) {
      _notifications = raw.map((n) {
        if (n is AppNotification) return n;
        if (n is Map) {
          return AppNotification(
            title: n['title']?.toString() ?? 'Notification',
            body: n['body']?.toString() ?? n['message']?.toString() ?? '',
            time: n['time']?.toString() ?? n['created_at']?.toString() ?? 'Just now',
            type: n['type']?.toString() ?? 'broadcast',
            isRead: n['is_read'] == true,
          );
        }
        return AppData.notifications.first;
      }).toList();
    } else {
      _notifications = List.from(AppData.notifications);
    }
  }

  void _markAllRead() {
    setState(() {
      _notifications = _notifications
          .map((n) => AppNotification(
                title: n.title,
                body: n.body,
                time: n.time,
                type: n.type,
                isRead: true,
              ))
          .toList();
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('All notifications marked as read ✅'),
        duration: Duration(seconds: 1),
      ),
    );
  }

  IconData _icon(String type) {
    switch (type) {
      case 'assignment':
        return Icons.assignment_outlined;
      case 'meeting':
        return Icons.people_outline_rounded;
      case 'exam':
        return Icons.quiz_outlined;
      case 'attendance':
        return Icons.how_to_reg_outlined;
      case 'message':
        return Icons.chat_bubble_outline_rounded;
      default:
        return Icons.notifications_outlined;
    }
  }

  Color _color(String type) {
    switch (type) {
      case 'assignment':
        return AppTheme.blue;
      case 'meeting':
        return AppTheme.purple;
      case 'exam':
        return AppTheme.orange;
      case 'attendance':
        return AppTheme.green;
      case 'message':
        return AppTheme.primary;
      default:
        return AppTheme.darkSubtext;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacherProvider = Provider.of<TeacherProvider>(context);
    if (!_initialized) {
      _initNotifications(teacherProvider.notifications);
      _initialized = true;
    }

    final displayed = _notifications.where((n) {
      if (_selectedFilter == 1) return !n.isRead;
      if (_selectedFilter == 2) return n.type == 'exam' || n.type == 'meeting';
      return true;
    }).toList();

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Notifications (सूचना तथा सूचनाहरू)',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
            ),
            Text(
              'Real-Time Teacher Broadcasts',
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
          TextButton(
            onPressed: _markAllRead,
            child: const Text(
              'Mark all read',
              style: TextStyle(
                color: AppTheme.primary,
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Column(
        children: [
          // Filter Chips
          Container(
            height: 40,
            margin: const EdgeInsets.symmetric(vertical: 8),
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 18),
              itemCount: _filters.length,
              itemBuilder: (_, i) {
                final sel = _selectedFilter == i;
                return GestureDetector(
                  onTap: () => setState(() => _selectedFilter = i),
                  child: Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: sel
                          ? AppTheme.primary
                          : (isDark ? AppTheme.darkCard : Colors.white),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: sel
                            ? AppTheme.primary
                            : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                      ),
                    ),
                    child: Center(
                      child: Text(
                        _filters[i],
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: sel
                              ? Colors.white
                              : (isDark ? Colors.white70 : AppTheme.dark),
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),

          // Notification List
          Expanded(
            child: displayed.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.notifications_off_outlined,
                            size: 52, color: AppTheme.darkSubtext.withOpacity(0.5)),
                        const SizedBox(height: 12),
                        const Text(
                          'No notifications found',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
                        ),
                      ],
                    ),
                  )
                : ListView.builder(
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(18, 4, 18, 90),
                    itemCount: displayed.length,
                    itemBuilder: (context, i) {
                      final item = displayed[i];
                      final icon = _icon(item.type);
                      final color = _color(item.type);

                      return _NotifTile(
                        n: item,
                        icon: icon,
                        color: color,
                        onTap: () {
                          setState(() {
                            final idx = _notifications.indexOf(item);
                            if (idx != -1) {
                              _notifications[idx] = AppNotification(
                                title: item.title,
                                body: item.body,
                                time: item.time,
                                type: item.type,
                                isRead: true,
                              );
                            }
                          });
                        },
                      ).animate().fadeIn(delay: (i * 30).ms);
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

class _NotifTile extends StatelessWidget {
  final AppNotification n;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;
  const _NotifTile({
    required this.n,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: n.isRead
              ? (isDark ? AppTheme.darkCard : Colors.white)
              : (isDark ? AppTheme.primary.withOpacity(0.12) : const Color(0xFFEFF6FF)),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: n.isRead
                ? (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder)
                : AppTheme.primary.withOpacity(0.35),
            width: n.isRead ? 1 : 1.3,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.2 : 0.02),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          n.title,
                          style: TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w800,
                            color: isDark ? Colors.white : AppTheme.dark,
                          ),
                        ),
                      ),
                      if (!n.isRead)
                        Container(
                          width: 8,
                          height: 8,
                          margin: const EdgeInsets.only(left: 6),
                          decoration: const BoxDecoration(
                            color: AppTheme.primary,
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    n.body,
                    style: TextStyle(
                      fontSize: 12,
                      color: isDark ? Colors.white70 : AppTheme.darkSubtext,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    n.time,
                    style: const TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.darkSubtext,
                    ),
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
