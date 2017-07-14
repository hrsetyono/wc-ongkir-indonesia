### 1.1.0

- Combine City and District into one SELECT, separated with optgroup

### 1.0.0-beta5

- Removed the function that reorders checkout field.
- Fixed an error when choosing City Origin.
- Removed City Origin cache since it's no longer do API call to get the list.

### 1.0.0-beta2

- Fixed a bug where Shipping cost doesn't initially load when customer has existing address.

### 1.0.0-beta

- City and District no longer uses API. All data stored internally in JSON.
- Added RPX, J&T, and PCP couriers.

### 0.3.6

- Added City and District data as part of the plugin. No need to do API call just to get them.

### 0.3.5b

- Fixed Shipping Zone can't be removed

### 0.3.5

- Fixed City origin not saving due to Enhanced Select bug.
- Add comment and add more PHPdoc

### 0.3.4

- Add prefix to city with same name. Example: "Bandung (Kota)" and "Bandung (Kabupaten)"

### 0.3.3

- Fix for initial installation

### 0.3.2

- Directory structure changes
- Namespace the landing code with class

### 0.3.1

- Fixed plugin not working on new website.
- Added Global settings, you still need to set individual shipping zones for it to work.

### 0.3.0

- Added support to Shipping Zone for WooCommerce 2.6
- Remove select2 on City and District field

### 0.2.1

- Refactor open function name into Class.
- Fix bug when using WooCommerce 2.6

### 0.2.0

- Fully functional
