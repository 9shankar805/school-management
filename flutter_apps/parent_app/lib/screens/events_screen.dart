import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class EventsScreen extends StatefulWidget {
  const EventsScreen({super.key});
  @override
  State<EventsScreen> createState() => _EventsScreenState();
}

class _EventsScreenState extends State<EventsScreen> {
  String _selectedCategory = 'All';

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final allEvents = api.events;

    final categories = ['All', 'Academic', 'Cultural Event', 'Holiday'];
    final events = allEvents.where((e) {
      if (_selectedCategory == 'All') return true;
      return e.category.toLowerCase().contains(_selectedCategory.toLowerCase());
    }).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Events & School Calendar'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
      ),
      body: Column(
        children: [
          // Category Selector Pills
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
            child: Row(
              children: categories.map((cat) {
                final isSel = cat == _selectedCategory;
                return GestureDetector(
                  onTap: () => setState(() => _selectedCategory = cat),
                  child: Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    decoration: BoxDecoration(
                      color: isSel ? AppTheme.primary : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: isSel ? AppTheme.primary : const Color(0xFFE5E9F0)),
                    ),
                    child: Text(
                      cat,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: isSel ? Colors.white : AppTheme.dark,
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          Expanded(
            child: events.isEmpty
                ? const EmptyStateWidget(
                    icon: Icons.event_note_outlined,
                    title: 'No events found',
                    subtitle: 'Check back later for updated school calendar announcements.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.fromLTRB(20, 4, 20, 40),
                    itemCount: events.length,
                    itemBuilder: (context, idx) {
                      final ev = events[idx];
                      return _EventCard(event: ev).animate().fadeIn(delay: (idx * 40).ms);
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

class _EventCard extends StatefulWidget {
  final EventItem event;
  const _EventCard({required this.event});

  @override
  State<_EventCard> createState() => _EventCardState();
}

class _EventCardState extends State<_EventCard> {
  late String _rsvp;

  @override
  void initState() {
    super.initState();
    _rsvp = widget.event.rsvpStatus;
  }

  @override
  Widget build(BuildContext context) {
    final e = widget.event;

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
                    color: Color(e.colorHex).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    e.category.toUpperCase(),
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(e.colorHex)),
                  ),
                ),
                if (e.isHoliday)
                  const StatusBadge(label: 'OFFICIAL HOLIDAY', color: AppTheme.green)
                else
                  Row(
                    children: [
                      _RsvpPill(
                        label: 'Going 👍',
                        isSelected: _rsvp == 'going',
                        onTap: () {
                          setState(() => _rsvp = 'going');
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('RSVP marked as Going! Added to student calendar.')),
                          );
                        },
                      ),
                      const SizedBox(width: 6),
                      _RsvpPill(
                        label: 'Maybe',
                        isSelected: _rsvp == 'maybe',
                        onTap: () => setState(() => _rsvp = 'maybe'),
                      ),
                    ],
                  ),
              ],
            ),
            const SizedBox(height: 10),
            Text(e.title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
            const SizedBox(height: 4),
            Text(e.description, style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
            const Divider(height: 20),
            Row(
              children: [
                const Icon(Icons.calendar_month_outlined, size: 14, color: AppTheme.grey),
                const SizedBox(width: 4),
                Text(e.startDate, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                const SizedBox(width: 14),
                const Icon(Icons.access_time, size: 14, color: AppTheme.grey),
                const SizedBox(width: 4),
                Text(e.time, style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
              ],
            ),
            const SizedBox(height: 6),
            Row(
              children: [
                const Icon(Icons.location_on_outlined, size: 14, color: AppTheme.primary),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    e.location,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.primary),
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

class _RsvpPill extends StatelessWidget {
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const _RsvpPill({required this.label, required this.isSelected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.primary : AppTheme.lightBg,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w800,
            color: isSelected ? Colors.white : AppTheme.dark,
          ),
        ),
      ),
    );
  }
}
