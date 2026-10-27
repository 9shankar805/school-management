import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // Veda Signature Navy & Ocean Blues
  static const Color primary = Color(0xFF0F4C81); // Deep Veda Royal Blue
  static const Color primaryDark = Color(0xFF0A2E50);
  static const Color primaryLight = Color(0xFFE8F1FA);
  static const Color secondary = Color(0xFF00A896); // Vibrant Teal / Emerald
  static const Color accent = Color(0xFF2563EB); // Vibrant Electric Blue
  
  // Neutral Tones
  static const Color dark = Color(0xFF0F172A); // Slate 900
  static const Color darkSubtext = Color(0xFF64748B); // Slate 500
  static const Color grey = Color(0xFF94A3B8); // Slate 400
  static const Color lightBg = Color(0xFFF1F5F9); // Crisp Slate 100
  static const Color cardBg = Colors.white;
  static const Color cardBorder = Color(0xFFE2E8F0);

  // Status & Feature Palette
  static const Color green = Color(0xFF10B981); // Emerald 500
  static const Color greenLight = Color(0xFFD1FAE5);
  static const Color orange = Color(0xFFF59E0B); // Amber 500
  static const Color orangeLight = Color(0xFFFEF3C7);
  static const Color gold = Color(0xFFF59E0B); // Gold / Amber 500
  static const Color blue = Color(0xFF3B82F6); // Blue 500
  static const Color blueLight = Color(0xFFDBEAFE);
  static const Color purple = Color(0xFF8B5CF6); // Purple 500
  static const Color purpleLight = Color(0xFFEDE9FE);
  static const Color teal = Color(0xFF14B8A6);
  static const Color tealLight = Color(0xFFCCFBF1);
  static const Color red = Color(0xFFEF4444); // Rose 500
  static const Color redLight = Color(0xFFFEE2E2);
  static const Color pink = Color(0xFFEC4899);
  static const Color pinkLight = Color(0xFFFCE7F3);

  // Dark Theme Tokens
  static const Color darkBg = Color(0xFF0B1120);
  static const Color darkCard = Color(0xFF1E293B);
  static const Color darkSurface = Color(0xFF334155);
  static const Color darkCardBorder = Color(0xFF334155);

  static ThemeData lightTheme = ThemeData(
    useMaterial3: true,
    brightness: Brightness.light,
    textTheme: GoogleFonts.plusJakartaSansTextTheme(),
    scaffoldBackgroundColor: lightBg,
    colorScheme: ColorScheme.fromSeed(
      seedColor: primary,
      primary: primary,
      secondary: secondary,
      surface: cardBg,
      brightness: Brightness.light,
    ),
    appBarTheme: AppBarTheme(
      backgroundColor: Colors.transparent,
      elevation: 0,
      centerTitle: true,
      titleTextStyle: GoogleFonts.plusJakartaSans(
        color: dark,
        fontSize: 18,
        fontWeight: FontWeight.w700,
      ),
      iconTheme: const IconThemeData(color: dark),
    ),
  );

  static ThemeData darkTheme = ThemeData(
    useMaterial3: true,
    brightness: Brightness.dark,
    textTheme: GoogleFonts.plusJakartaSansTextTheme(ThemeData.dark().textTheme),
    scaffoldBackgroundColor: darkBg,
    colorScheme: ColorScheme.fromSeed(
      seedColor: primary,
      primary: primary,
      secondary: secondary,
      surface: darkCard,
      brightness: Brightness.dark,
    ),
    appBarTheme: AppBarTheme(
      backgroundColor: Colors.transparent,
      elevation: 0,
      centerTitle: true,
      titleTextStyle: GoogleFonts.plusJakartaSans(
        color: Colors.white,
        fontSize: 18,
        fontWeight: FontWeight.w700,
      ),
      iconTheme: const IconThemeData(color: Colors.white),
    ),
  );
}
