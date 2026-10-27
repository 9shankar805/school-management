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
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(color: Colors.white.withOpacity(0.2), borderRadius: BorderRadius.circular(10)),
                        child: const Text('Exemplary Status 🌟', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _MeritStat(label: 'Total Merits', value: '+$totalPoints pts'),
                      _MeritStat(label: 'Warnings', value: '0 active'),
                      _MeritStat(label: 'Badge Tier', value: 'Gold Leader'),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05),
            const SizedBox(height: 24),

            const SectionHeader(title: '🏆 Commendations & Teacher Notes'),
            const SizedBox(height: 12),
            if (entries.isEmpty)
              const EmptyStateWidget(
                icon: Icons.emoji_events_outlined,
                title: 'No behavior entries recorded',
                subtitle: 'Teacher recognitions and merit awards will be logged here.',
              )
            else
              ...entries.map((entry) => _BehaviorCard(entry: entry)),
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

class _BehaviorCard extends StatelessWidget {
  final BehaviorEntry entry;
  const _BehaviorCard({required this.entry});

  @override
  Widget build(BuildContext context) {
    final isMerit = entry.type == 'merit';

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
                    color: Color(entry.colorHex).withOpacity(0.12),
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
                Text(entry.date, style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
