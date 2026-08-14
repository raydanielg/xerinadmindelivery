import 'package:flutter/material.dart';

import 'custom_theme_colors.dart';

ThemeData darkTheme = ThemeData(
  fontFamily: 'SFProText',
  primaryColor: const Color(0xFFFF6B00),
  brightness: Brightness.dark,
  cardColor: const Color(0xFF242424),
  hintColor: const Color(0xFF9F9F9F),
  scaffoldBackgroundColor: const Color(0xFF1C1F1F),
  primaryColorDark: const Color(0xffCC5500),

    extensions: <ThemeExtension<CustomThemeColors>>[
      CustomThemeColors.dark()
    ],


  colorScheme: const ColorScheme.dark(
      primary: Color(0xFFFF8C33),
      error: Color(0xFFFF6767),
      secondary: Color(0xFFE55A00),
      tertiary: Color(0xFF7CCD8B),
      tertiaryContainer: Color(0xFFC98B3E),
      secondaryContainer: Color(0xFFEE6464),
      onTertiary: Color(0xFFD9D9D9),
      onSecondary: Color(0xFFFF6B00),
      onSecondaryContainer: Color(0xFFA8C5C1),
      onTertiaryContainer: Color(0xFF425956),
      outline: Color(0xFFFFCC99),
      onPrimaryContainer: Color(0xFF929494),
      primaryContainer: Color(0xFFFF6B00),
      onSurface: Color(0xFFFFE6AD),
      onPrimary: Color(0xFFCC5500),
      surfaceContainer: Color(0xFF016ACD),
      secondaryFixedDim: Color(0xFF808080)

  ),
  textButtonTheme: TextButtonThemeData(style: TextButton.styleFrom(foregroundColor: const Color(0xFFFF6B00))),
    textTheme: const TextTheme(
      displayLarge: TextStyle(fontWeight: FontWeight.w300, color: Color(0xFF202020)),
      displayMedium: TextStyle(fontWeight: FontWeight.w300, color: Color(0xFF393939)),
      displaySmall: TextStyle(fontWeight: FontWeight.w300, color: Color(0xFF282828)),
      bodyLarge: TextStyle(fontWeight: FontWeight.w300, color: Color(0xFF272727)),
      bodyMedium: TextStyle(fontWeight: FontWeight.w300, color: Color(0xffffffff)),
      bodySmall: TextStyle(fontWeight: FontWeight.w300, color: Color(0xFF1D2D2B)),
    )
);
