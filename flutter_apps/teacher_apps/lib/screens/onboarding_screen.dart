import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme/app_theme.dart';
import 'login_screen.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});
  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _controller = PageController();
  int _currentPage = 0;

  final List<_OnboardData> _pages = const [
    _OnboardData(
      title: 'Manage Your\nClasses\nEffortlessly',
      subtitle: 'All your classes, students, and schedules in one place.',
      icon: Icons.class_rounded,
      gradient: [Color(0xFF1565C0), Color(0xFF1E88E5)],
    ),
    _OnboardData(
      title: 'Track Attendance\n& Grades\nInstantly',
      subtitle: 'Mark attendance, update grades, and monitor progress with ease.',
      icon: Icons.how_to_reg_rounded,
      gradient: [Color(0xFF0097A7), Color(0xFF26C6DA)],
    ),
    _OnboardData(
      title: 'Communicate\nWith Students\n& Parents',
      subtitle: 'Send announcements, chat directly, and stay connected.',
      icon: Icons.campaign_rounded,
      gradient: [Color(0xFF7B1FA2), Color(0xFFAB47BC)],
    ),
    _OnboardData(
      title: 'Never Miss\nAn Exam or\nDeadline',
      subtitle: 'Smart reminders for exams, assignments, and parent meetings.',
      icon: Icons.notifications_active_rounded,
      gradient: [Color(0xFF2E7D32), Color(0xFF66BB6A)],
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(children: [
          Expanded(
            child: PageView.builder(
              controller: _controller,
              onPageChanged: (i) => setState(() => _currentPage = i),
              itemCount: _pages.length,
              itemBuilder: (_, i) => _OnboardPage(data: _pages[i]),
            ),
          ),
          _BottomControls(
            currentPage: _currentPage,
            total: _pages.length,
            onNext: () {
              if (_currentPage < _pages.length - 1) {
                _controller.nextPage(
                    duration: const Duration(milliseconds: 400),
                    curve: Curves.easeInOut);
              } else {
                Navigator.pushReplacement(context,
                    MaterialPageRoute(builder: (_) => const LoginScreen()));
              }
            },
            onSkip: () => Navigator.pushReplacement(context,
                MaterialPageRoute(builder: (_) => const LoginScreen())),
          ),
        ]),
      ),
    );
  }
}

class _OnboardData {
  final String title, subtitle;
  final IconData icon;
  final List<Color> gradient;
  const _OnboardData(
      {required this.title, required this.subtitle,
      required this.icon, required this.gradient});
}

class _OnboardPage extends StatelessWidget {
  final _OnboardData data;
  const _OnboardPage({required this.data});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 28),
      child: Column(children: [
        const SizedBox(height: 40),
        Expanded(
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                  colors: data.gradient,
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight),
              borderRadius: BorderRadius.circular(40),
            ),
            child: Stack(alignment: Alignment.center, children: [
              Positioned(top: 20, right: 20,
                child: Container(width: 60, height: 60,
                    decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.15),
                        shape: BoxShape.circle))),
              Positioned(bottom: 30, left: 20,
                child: Container(width: 40, height: 40,
                    decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.1),
                        shape: BoxShape.circle))),
              Icon(data.icon, size: 120, color: Colors.white.withOpacity(0.9)),
            ]),
          ).animate().scale(duration: 500.ms, curve: Curves.easeOut),
        ),
        const SizedBox(height: 36),
        Text(data.title,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w800,
                color: AppTheme.dark, height: 1.2))
            .animate().slideY(begin: 0.2, duration: 400.ms).fadeIn(),
        const SizedBox(height: 14),
        Text(data.subtitle,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 14, color: AppTheme.grey, height: 1.6))
            .animate().fadeIn(duration: 400.ms, delay: 200.ms),
        const SizedBox(height: 32),
      ]),
    );
  }
}

class _BottomControls extends StatelessWidget {
  final int currentPage, total;
  final VoidCallback onNext, onSkip;
  const _BottomControls(
      {required this.currentPage, required this.total,
      required this.onNext, required this.onSkip});

  @override
  Widget build(BuildContext context) {
    final isLast = currentPage == total - 1;
    return Padding(
      padding: const EdgeInsets.fromLTRB(28, 0, 28, 36),
      child: Row(children: [
        if (!isLast)
          TextButton(
            onPressed: onSkip,
            child: const Text('Skip',
                style: TextStyle(color: AppTheme.grey, fontSize: 15)),
          )
        else
          const SizedBox(width: 60),
        const Spacer(),
        Row(children: List.generate(total, (i) => AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          margin: const EdgeInsets.symmetric(horizontal: 3),
          width: i == currentPage ? 20 : 7, height: 7,
          decoration: BoxDecoration(
            color: i == currentPage
                ? AppTheme.primary : AppTheme.grey.withOpacity(0.3),
            borderRadius: BorderRadius.circular(4),
          ),
        ))),
        const Spacer(),
        GestureDetector(
          onTap: onNext,
          child: Container(
            width: 56, height: 56,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                  colors: [AppTheme.primary, AppTheme.secondary]),
              shape: BoxShape.circle,
              boxShadow: [BoxShadow(
                  color: AppTheme.primary.withOpacity(0.4),
                  blurRadius: 16, offset: const Offset(0, 6))],
            ),
            child: Icon(
              isLast ? Icons.check_rounded : Icons.arrow_forward_rounded,
              color: Colors.white, size: 26,
            ),
          ),
        ),
      ]),
    );
  }
}
