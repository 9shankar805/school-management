import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class BehaviorScreen extends StatefulWidget {
  const BehaviorScreen({super.key});
  @override
  State<BehaviorScreen> createState() => _BehaviorScreenState();
}

class _BehaviorScreenState extends State<BehaviorScreen> {
  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final entries = api.behaviorRecords;

    final int totalPoints = entries.fold<int>(0, (sum, e) => sum + e.points);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Behavior & Conduct'),
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
            // Merit Score Hero Card
            GradientCard(
              colors: const [Color(0xFF6C3CE9), Color(0xFF00D2D3)],
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Conduct Ledger • ${child.name}',
                        style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.22),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: const Text('Exemplary Status 🌟', style: TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.w800)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _MeritStat(label: 'Total Merits', value: '+$totalPoints pts'),
                      const _MeritStat(label: 'Warnings', value: '0 active'),
                      const _MeritStat(label: 'Badge Tier', value: 'Gold Leader'),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05),
            const SizedBox(height: 20),

            // Badges & Honors Gallery
            const SectionHeader(title: '🏅 Badges & Honors Won'),
            const SizedBox(height: 10),
            const SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _BadgePill(icon: Icons.star_rounded, title: 'Star Student', subtitle: 'Academic Excellence', color: Color(0xFFF59E0B)),
                  SizedBox(width: 10),
                  _BadgePill(icon: Icons.schedule_rounded, title: '100% Punctual', subtitle: 'Zero Late Arrivals', color: Color(0xFF10B981)),
                  SizedBox(width: 10),
                  _BadgePill(icon: Icons.psychology_rounded, title: 'STEM Leader', subtitle: 'Science Exhibition', color: Color(0xFF6366F1)),
                  SizedBox(width: 10),
                  _BadgePill(icon: Icons.volunteer_activism_rounded, title: 'Peer Mentor', subtitle: 'Helped Classmates', color: Color(0xFFEC4899)),
                ],
              ),
            ),
            const SizedBox(height: 24),

            const SectionHeader(title: '🏆 Commendations & Educator Notes'),
            const SizedBox(height: 12),
            if (entries.isEmpty)
              const EmptyStateWidget(
                icon: Icons.emoji_events_outlined,
                title: 'No behavior entries recorded',
                subtitle: 'Teacher recognitions and merit awards will be logged here.',
              )
            else
              ...entries.map((entry) => _BehaviorCard(
                    entry: entry,
                    onTap: () => _showEntryDetail(context, entry),
                  )),
          ],
        ),
      ),
    );
  }

  void _showEntryDetail(BuildContext context, BehaviorEntry entry) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24.0),
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
                    color: Color(entry.colorHex).withValues(alpha: 0.14),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    entry.category.toUpperCase(),
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(entry.colorHex)),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.greenLight,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '+${entry.points} Merit Points',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: AppTheme.green),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(entry.title, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
            const SizedBox(height: 10),
            Text(
              entry.description,
              style: const TextStyle(fontSize: 13, color: AppTheme.dark, height: 1.4),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  const Icon(Icons.person, color: AppTheme.primary, size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Awarded by: ${entry.teacherName}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                        Text('Recorded on: ${entry.date}', style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Close'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MeritStat extends StatelessWidget {
  final String label, value;
  const _MeritStat({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(value, style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.w900)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.w600)),
      ],
    );
  }
}

class _BadgePill extends StatelessWidget {
  final IconData icon;
  final String title, subtitle;
  final Color color;

  const _BadgePill({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 6),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.14),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
              Text(subtitle, style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
            ],
          ),
        ],
      ),
    );
  }
}

class _BehaviorCard extends StatelessWidget {
  final BehaviorEntry entry;
  final VoidCallback onTap;

  const _BehaviorCard({required this.entry, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final isMerit = entry.type == 'merit';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: AppCard(
        padding: const EdgeInsets.all(16),
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Color(entry.colorHex).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    entry.category.toUpperCase(),
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(entry.colorHex)),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: isMerit ? AppTheme.greenLight : AppTheme.redLight,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    isMerit ? '+${entry.points} Points' : '${entry.points} Points',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      color: isMerit ? AppTheme.green : AppTheme.red,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(entry.title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
            const SizedBox(height: 4),
            Text(entry.description, style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.person_outline, size: 14, color: AppTheme.grey),
                    const SizedBox(width: 4),
                    Text(entry.teacherName, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
                  ],
                ),
                Row(
                  children: [
                    Text(entry.date, style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                    const SizedBox(width: 4),
                    const Icon(Icons.chevron_right, size: 14, color: AppTheme.grey),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
