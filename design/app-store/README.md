# App Store header artwork

Real app screens (iPhone 17 Pro simulator, demo shop "Duka la Mama Neema", fake data) in a CSS device frame.

- `final/sasampa_header_universal_5244x2950.png` - one 16:9 image for both placements (tick "Use header asset in search results")
- `final/sasampa_header_21x9_3840x1646.png` - product page header
- `final/sasampa_search_3x2_3840x2560.png` - search results card
- All are RGB PNGs with no alpha, as Apple requires. The 21:9 and 3:2 files are centred crops of the master.

To change the artwork: edit `source/header.html` and render it at 5244x2950 (see the command in git history),
then crop with the same centred boxes. Screens in `source/` come from `mobile_app/integration_test/store_shots_test.dart`.
