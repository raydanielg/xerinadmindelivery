import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:ride_sharing_user_app/features/out_of_zone/controllers/out_of_zone_controller.dart';
import 'package:ride_sharing_user_app/helper/login_helper.dart';
import 'package:ride_sharing_user_app/util/dimensions.dart';
import 'package:ride_sharing_user_app/util/images.dart';
import 'package:ride_sharing_user_app/util/styles.dart';
import 'package:ride_sharing_user_app/features/auth/controllers/auth_controller.dart';
import 'package:ride_sharing_user_app/features/dashboard/controllers/bottom_menu_controller.dart';
import 'package:ride_sharing_user_app/features/dashboard/screens/dashboard_screen.dart';
import 'package:ride_sharing_user_app/features/location/controllers/location_controller.dart';
import 'package:ride_sharing_user_app/common_widgets/button_widget.dart';
import 'package:ride_sharing_user_app/common_widgets/loader_widget.dart';
import 'package:ride_sharing_user_app/theme/theme_controller.dart';


class AccessLocationScreen extends StatefulWidget {
  const AccessLocationScreen({super.key});

  @override
  State<AccessLocationScreen> createState() => _AccessLocationScreenState();
}

class _AccessLocationScreenState extends State<AccessLocationScreen> with WidgetsBindingObserver {
  bool _isCheckingPermission = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed && !_isCheckingPermission) {
      _isCheckingPermission = true;
      Get.find<LocationController>().checkPermission().then((granted) {
        _isCheckingPermission = false;
        if (granted && mounted) {
          Get.dialog(const LoaderWidget(), barrierDismissible: false);
          Get.find<LocationController>().getCurrentLocation().then((value) {
            Get.back();
            if (value.latitude != 0 && value.longitude != 0) {
              if (Get.find<AuthController>().isLoggedIn()) {
                Get.find<OutOfZoneController>().getZoneList();
                Get.offAll(() => const DashboardScreen());
              } else {
                LoginHelper.checkLoginMedium();
              }
            }
          }).catchError((_) {
            Get.back();
          });
        }
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Scaffold(
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        body: PopScope(canPop: false,
          onPopInvokedWithResult: (res, val) async {
            Get.find<BottomMenuController>().exitApp();
            return;
          },
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: Get.isDarkMode
                    ? [Theme.of(context).primaryColorDark, Theme.of(context).scaffoldBackgroundColor]
                    : [Theme.of(context).primaryColor.withValues(alpha: 0.1), Theme.of(context).scaffoldBackgroundColor],
              ),
            ),
            child: Center(
              child: GetBuilder<LocationController>(builder: (locationController) {
                return Column(children: [
                  Expanded(child: SizedBox(
                    width: Dimensions.webMaxWidth,
                    child: Center(
                      child: SingleChildScrollView(
                        child: SizedBox(
                          width: 700,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(Dimensions.paddingSizeExtraLarge),
                                decoration: BoxDecoration(
                                  color: Theme.of(context).cardColor,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: Theme.of(context).primaryColor.withValues(alpha: 0.2),
                                      blurRadius: 30,
                                      spreadRadius: 5,
                                    ),
                                  ],
                                ),
                                child: Image.asset(
                                  Get.find<ThemeController>().darkTheme ? Images.logoDarkMode : Images.logoLightMode,
                                  height: 80,
                                ),
                              ),
                              const SizedBox(height: Dimensions.paddingSizeExtraLarge),

                              Text(
                                'enable_location_access'.tr,
                                textAlign: TextAlign.center,
                                style: textBold.copyWith(
                                  fontSize: 24,
                                  color: Get.isDarkMode
                                      ? Theme.of(context).primaryColorLight
                                      : Theme.of(context).primaryColor,
                                ),
                              ),
                              const SizedBox(height: Dimensions.paddingSizeDefault),

                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraLarge),
                                child: Text(
                                  'location_access_description'.tr,
                                  textAlign: TextAlign.center,
                                  style: textRegular.copyWith(
                                    fontSize: Dimensions.fontSizeSmall,
                                    color: Get.isDarkMode
                                        ? Theme.of(context).primaryColorLight?.withValues(alpha: 0.8)
                                        : Theme.of(context).textTheme.bodyMedium?.color?.withValues(alpha: 0.7),
                                  ),
                                ),
                              ),
                              const SizedBox(height: Dimensions.paddingSizeExtraLarge),

                              _LocationFeatureItem(
                                icon: Icons.near_me_rounded,
                                title: 'real_time_tracking'.tr,
                                description: 'track_deliveries_real_time'.tr,
                              ),
                              const SizedBox(height: Dimensions.paddingSizeDefault),

                              _LocationFeatureItem(
                                icon: Icons.local_shipping_rounded,
                                title: 'nearby_delivery_requests'.tr,
                                description: 'receive_delivery_requests_nearby'.tr,
                              ),
                              const SizedBox(height: Dimensions.paddingSizeDefault),

                              _LocationFeatureItem(
                                icon: Icons.navigation_rounded,
                                title: 'optimized_routes'.tr,
                                description: 'get_best_routes_to_pickup'.tr,
                              ),
                              const SizedBox(height: Dimensions.paddingSizeExtraLarge),

                              const BottomButton(),
                            ],
                          ),
                        ),
                      ),
                    ),
                  )),
                ],
                );
              }),
            ),
          ),
        ),
      ),
    );
  }
}

class _LocationFeatureItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final String description;

  const _LocationFeatureItem({
    required this.icon,
    required this.title,
    required this.description,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraLarge),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            decoration: BoxDecoration(
              color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
            ),
            child: Icon(icon, color: Theme.of(context).primaryColor, size: 24),
          ),
          const SizedBox(width: Dimensions.paddingSizeDefault),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: textBold.copyWith(fontSize: 14)),
                const SizedBox(height: 2),
                Text(
                  description,
                  style: textRegular.copyWith(
                    fontSize: 11,
                    color: Theme.of(context).textTheme.bodyMedium?.color?.withValues(alpha: 0.6),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class BottomButton extends StatelessWidget {
  const BottomButton({super.key});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SizedBox(
        width: MediaQuery.of(context).size.width - 40,
        child: Column(
          children: [
            ButtonWidget(
              buttonText: 'allow_location_access'.tr,
              fontSize: Dimensions.fontSizeSmall,
              onPressed: () async {
                Get.find<LocationController>().checkPermission().then((permission) {
                  if (permission) {
                    Get.dialog(const LoaderWidget(), barrierDismissible: false);
                    Get.find<LocationController>().getCurrentLocation().then((value) {
                      Get.back();
                      if (value.latitude != 0 && value.longitude != 0) {
                        if (Get.find<AuthController>().isLoggedIn()) {
                          Get.find<OutOfZoneController>().getZoneList();
                          Get.offAll(() => const DashboardScreen());
                        } else {
                          LoginHelper.checkLoginMedium();
                        }
                      }
                    });
                  }
                });
              },
              icon: Icons.my_location,
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            TextButton(
              onPressed: () {
                Get.find<BottomMenuController>().exitApp();
              },
              child: Text(
                'maybe_later'.tr,
                style: textRegular.copyWith(
                  fontSize: Dimensions.fontSizeSmall,
                  color: Theme.of(context).textTheme.bodyMedium?.color?.withValues(alpha: 0.5),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

