import 'package:get/get.dart';
import 'package:ride_sharing_user_app/features/auth/domain/models/error_response.dart';
import 'package:ride_sharing_user_app/features/splash/controllers/config_controller.dart';
import 'package:ride_sharing_user_app/helper/display_helper.dart';
import 'package:ride_sharing_user_app/helper/login_helper.dart';

class ApiChecker {
  static void checkApi(Response response) {
    if(response.statusCode == 401) {
      Get.find<ConfigController>().removeSharedData();
      LoginHelper.checkLoginMedium();

    }else if(response.statusCode == 403) {
      try {
        ErrorResponse errorResponse;
        errorResponse = ErrorResponse.fromJson(response.body);
        if(errorResponse.errors != null && errorResponse.errors!.isNotEmpty){
          showCustomSnackBar(errorResponse.errors![0].message!);
        }else{
          showCustomSnackBar(response.body['message']);
        }
      } catch(_) {
        showCustomSnackBar('Server error. Please try again later.');
      }

    }else if(response.statusCode == 422) {
      try {
        ErrorResponse errorResponse;
        errorResponse = ErrorResponse.fromJson(response.body);
        if(errorResponse.errors != null && errorResponse.errors!.isNotEmpty){
          showCustomSnackBar(errorResponse.errors![0].message!);
        }else{
          showCustomSnackBar(response.body['message']);
        }
      } catch(_) {
        showCustomSnackBar('Validation error. Please check your input.');
      }

    }else if(response.statusCode == 500){
      showCustomSnackBar(response.statusText ?? 'Server error');
    }else {
      showCustomSnackBar(response.statusText ?? 'Something went wrong');
    }
  }
}
