import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../services/api_service.dart';
import '../widgets/common_widgets.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});
  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  String _selectedFilter = 'All';

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final notifications = api.notifications;

    final categories = ['All', 'bus', 'grade', 'fee', 'general'];
    final filtered = notifications.where((n) {
      if (_selectedFilter == 'All') return true;
      return n.type.toLowerCase() == _selectedFilter.toLowerCase();
    }).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications & Alerts'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          TextButton(
            onPressed: () async {
              await ApiService.markAllNotificationsRead();
              api.loadAllData();
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('All notifications marked as read.')),
                );
              }
            },
            child: const Text('Mark all read', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.primary)),
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter Tabs
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
            child: Row(
              children: categories.map((cat) {
                final isSel = cat == _selectedFilter;
                String label = cat.toUpperCase();
                if (cat == 'bus') label = 'TRANSPORT 🚌';
                if (cat == 'grade') label = 'ACADEMICS 📝';
                if (cat == 'fee') label = 'FEES 💳';

                return GestureDetector(
                  onTap: () => setState(() => _selectedFilter = cat),
                  child: Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: isSel ? AppTheme.primary : Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: isSel ? AppTheme.primary : const Color(0xFFE5E9F0)),
                    ),
                    child: Text(
                      label,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: isSel ? Colors.white : AppTheme.dark,
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          Expanded(
            child: filtered.isEmpty
                ? const EmptyStateWidget(
                    icon: Icons.notifications_none_rounded,
                    title: 'No alerts in this category',
                    subtitle: 'New notices and school push updates will appear here.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.fromLTRB(20, 4, 20, 40),
                    itemCount: filtered.length,
                    itemBuilder: (context, idx) {
                      final notif = filtered[idx];
                      return _NotificationCard(notification: notif).animate().fadeIn(delay: (idx * 40).ms);
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final AppNotification notification;
  const _NotificationCard({required this.notification});

  IconData get _icon {
    switch (notification.type.toLowerCase()) {
      case 'grade':
        return Icons.bar_chart_rounded;
      case 'bus':
        return Icons.directions_bus_rounded;
      case 'fee':
        return Icons.account_balance_wallet_rounded;
      case 'exam':
        return Icons.quiz_rounded;
      default:
        return Icons.campaign_rounded;
    }
  }

  Color get _color {
    switch (notification.type.toLowerCase()) {
      case 'grade':
        return AppTheme.blue;
      case 'bus':
        return AppTheme.teal;
      case 'fee':
        return AppTheme.orange;
      case 'exam':
        return AppTheme.purple;
      default:
        return AppTheme.primary;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      child: AppCard(
        padding: const EdgeInsets.all(14),
        border: !notification.isRead ? Border.all(color: _color.withOpacity(0.4), width: 1.5) : null,
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: _color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(_icon, color: _color, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          notification.title,
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: notification.isRead ? FontWeight.w700 : FontWeight.w900,
                          ),
                        ),
                      ),
                      Text(notification.time, style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    notification.body,
                    style: TextStyle(
                      fontSize: 12,
                      color: notification.isRead ? AppTheme.grey : AppTheme.dark,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Student: ${notification.childName}',
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: _color),
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
