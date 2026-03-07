import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_en.dart';
import 'app_localizations_sw.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'l10n/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
    : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
        delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
      ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[
    Locale('en'),
    Locale('sw'),
  ];

  /// No description provided for @appTitle.
  ///
  /// In sw, this message translates to:
  /// **'Sasampa POS'**
  String get appTitle;

  /// No description provided for @login.
  ///
  /// In sw, this message translates to:
  /// **'Ingia'**
  String get login;

  /// No description provided for @register.
  ///
  /// In sw, this message translates to:
  /// **'Jisajili'**
  String get register;

  /// No description provided for @email.
  ///
  /// In sw, this message translates to:
  /// **'Barua pepe'**
  String get email;

  /// No description provided for @password.
  ///
  /// In sw, this message translates to:
  /// **'Nywila'**
  String get password;

  /// No description provided for @confirmPassword.
  ///
  /// In sw, this message translates to:
  /// **'Thibitisha nywila'**
  String get confirmPassword;

  /// No description provided for @fullName.
  ///
  /// In sw, this message translates to:
  /// **'Jina kamili'**
  String get fullName;

  /// No description provided for @rememberEmail.
  ///
  /// In sw, this message translates to:
  /// **'Kumbuka barua pepe'**
  String get rememberEmail;

  /// No description provided for @dontHaveAccount.
  ///
  /// In sw, this message translates to:
  /// **'Huna akaunti?'**
  String get dontHaveAccount;

  /// No description provided for @alreadyHaveAccount.
  ///
  /// In sw, this message translates to:
  /// **'Una akaunti tayari?'**
  String get alreadyHaveAccount;

  /// No description provided for @createAccount.
  ///
  /// In sw, this message translates to:
  /// **'Fungua Akaunti'**
  String get createAccount;

  /// No description provided for @signIn.
  ///
  /// In sw, this message translates to:
  /// **'Ingia'**
  String get signIn;

  /// No description provided for @forgotPassword.
  ///
  /// In sw, this message translates to:
  /// **'Umesahau nywila?'**
  String get forgotPassword;

  /// No description provided for @dashboard.
  ///
  /// In sw, this message translates to:
  /// **'Dashibodi'**
  String get dashboard;

  /// No description provided for @pos.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo'**
  String get pos;

  /// No description provided for @transactions.
  ///
  /// In sw, this message translates to:
  /// **'Miamala'**
  String get transactions;

  /// No description provided for @orders.
  ///
  /// In sw, this message translates to:
  /// **'Oda'**
  String get orders;

  /// No description provided for @menu.
  ///
  /// In sw, this message translates to:
  /// **'Menyu'**
  String get menu;

  /// No description provided for @settings.
  ///
  /// In sw, this message translates to:
  /// **'Mipangilio'**
  String get settings;

  /// No description provided for @expenses.
  ///
  /// In sw, this message translates to:
  /// **'Matumizi'**
  String get expenses;

  /// No description provided for @inventory.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa'**
  String get inventory;

  /// No description provided for @search.
  ///
  /// In sw, this message translates to:
  /// **'Tafuta'**
  String get search;

  /// No description provided for @searchHint.
  ///
  /// In sw, this message translates to:
  /// **'Tafuta kwa nambari, mteja...'**
  String get searchHint;

  /// No description provided for @today.
  ///
  /// In sw, this message translates to:
  /// **'Leo'**
  String get today;

  /// No description provided for @thisWeek.
  ///
  /// In sw, this message translates to:
  /// **'Wiki Hii'**
  String get thisWeek;

  /// No description provided for @thisMonth.
  ///
  /// In sw, this message translates to:
  /// **'Mwezi Huu'**
  String get thisMonth;

  /// No description provided for @all.
  ///
  /// In sw, this message translates to:
  /// **'Zote'**
  String get all;

  /// No description provided for @allTime.
  ///
  /// In sw, this message translates to:
  /// **'Wakati Wote'**
  String get allTime;

  /// No description provided for @completeSale.
  ///
  /// In sw, this message translates to:
  /// **'Kamilisha Mauzo'**
  String get completeSale;

  /// No description provided for @saveAsOrder.
  ///
  /// In sw, this message translates to:
  /// **'Hifadhi Oda'**
  String get saveAsOrder;

  /// No description provided for @checkout.
  ///
  /// In sw, this message translates to:
  /// **'Lipia'**
  String get checkout;

  /// No description provided for @addToCart.
  ///
  /// In sw, this message translates to:
  /// **'Ongeza'**
  String get addToCart;

  /// No description provided for @cart.
  ///
  /// In sw, this message translates to:
  /// **'Kikapu'**
  String get cart;

  /// No description provided for @subtotal.
  ///
  /// In sw, this message translates to:
  /// **'Jumla ndogo'**
  String get subtotal;

  /// No description provided for @tax.
  ///
  /// In sw, this message translates to:
  /// **'Kodi'**
  String get tax;

  /// No description provided for @discount.
  ///
  /// In sw, this message translates to:
  /// **'Punguzo'**
  String get discount;

  /// No description provided for @total.
  ///
  /// In sw, this message translates to:
  /// **'Jumla'**
  String get total;

  /// No description provided for @amountPaid.
  ///
  /// In sw, this message translates to:
  /// **'Kiasi kilicholipwa'**
  String get amountPaid;

  /// No description provided for @change.
  ///
  /// In sw, this message translates to:
  /// **'Chenji'**
  String get change;

  /// No description provided for @paymentMethod.
  ///
  /// In sw, this message translates to:
  /// **'Njia ya malipo'**
  String get paymentMethod;

  /// No description provided for @cash.
  ///
  /// In sw, this message translates to:
  /// **'Taslimu'**
  String get cash;

  /// No description provided for @card.
  ///
  /// In sw, this message translates to:
  /// **'Kadi'**
  String get card;

  /// No description provided for @mobileMoney.
  ///
  /// In sw, this message translates to:
  /// **'Simu'**
  String get mobileMoney;

  /// No description provided for @bankTransfer.
  ///
  /// In sw, this message translates to:
  /// **'Benki'**
  String get bankTransfer;

  /// No description provided for @customerInfo.
  ///
  /// In sw, this message translates to:
  /// **'Taarifa za mteja'**
  String get customerInfo;

  /// No description provided for @customerName.
  ///
  /// In sw, this message translates to:
  /// **'Jina la mteja'**
  String get customerName;

  /// No description provided for @customerPhone.
  ///
  /// In sw, this message translates to:
  /// **'Simu ya mteja'**
  String get customerPhone;

  /// No description provided for @optional.
  ///
  /// In sw, this message translates to:
  /// **'Hiari'**
  String get optional;

  /// No description provided for @required.
  ///
  /// In sw, this message translates to:
  /// **'Lazima'**
  String get required;

  /// No description provided for @cancel.
  ///
  /// In sw, this message translates to:
  /// **'Ghairi'**
  String get cancel;

  /// No description provided for @confirm.
  ///
  /// In sw, this message translates to:
  /// **'Thibitisha'**
  String get confirm;

  /// No description provided for @delete.
  ///
  /// In sw, this message translates to:
  /// **'Futa'**
  String get delete;

  /// No description provided for @edit.
  ///
  /// In sw, this message translates to:
  /// **'Hariri'**
  String get edit;

  /// No description provided for @save.
  ///
  /// In sw, this message translates to:
  /// **'Hifadhi'**
  String get save;

  /// No description provided for @close.
  ///
  /// In sw, this message translates to:
  /// **'Funga'**
  String get close;

  /// No description provided for @retry.
  ///
  /// In sw, this message translates to:
  /// **'Jaribu tena'**
  String get retry;

  /// No description provided for @scanBarcode.
  ///
  /// In sw, this message translates to:
  /// **'Changanua msimbo'**
  String get scanBarcode;

  /// No description provided for @noProducts.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna bidhaa'**
  String get noProducts;

  /// No description provided for @noTransactions.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna miamala'**
  String get noTransactions;

  /// No description provided for @noOrders.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna oda'**
  String get noOrders;

  /// No description provided for @pending.
  ///
  /// In sw, this message translates to:
  /// **'Inasubiri'**
  String get pending;

  /// No description provided for @completed.
  ///
  /// In sw, this message translates to:
  /// **'Imekamilika'**
  String get completed;

  /// No description provided for @voided.
  ///
  /// In sw, this message translates to:
  /// **'Imefutwa'**
  String get voided;

  /// No description provided for @cancelled.
  ///
  /// In sw, this message translates to:
  /// **'Imesitishwa'**
  String get cancelled;

  /// No description provided for @convertToSale.
  ///
  /// In sw, this message translates to:
  /// **'Kamilisha Mauzo'**
  String get convertToSale;

  /// No description provided for @downloadProforma.
  ///
  /// In sw, this message translates to:
  /// **'Pakua Proforma'**
  String get downloadProforma;

  /// No description provided for @shareProforma.
  ///
  /// In sw, this message translates to:
  /// **'Shiriki Proforma'**
  String get shareProforma;

  /// No description provided for @proformaInvoice.
  ///
  /// In sw, this message translates to:
  /// **'Ankara ya Proforma'**
  String get proformaInvoice;

  /// No description provided for @receipt.
  ///
  /// In sw, this message translates to:
  /// **'Risiti'**
  String get receipt;

  /// No description provided for @printReceipt.
  ///
  /// In sw, this message translates to:
  /// **'Chapisha Risiti'**
  String get printReceipt;

  /// No description provided for @shareReceipt.
  ///
  /// In sw, this message translates to:
  /// **'Shiriki Risiti'**
  String get shareReceipt;

  /// No description provided for @voidTransaction.
  ///
  /// In sw, this message translates to:
  /// **'Futa Muamala'**
  String get voidTransaction;

  /// No description provided for @todaySales.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo ya Leo'**
  String get todaySales;

  /// No description provided for @weeklySales.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo ya Wiki'**
  String get weeklySales;

  /// No description provided for @monthlySales.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo ya Mwezi'**
  String get monthlySales;

  /// No description provided for @recentTransactions.
  ///
  /// In sw, this message translates to:
  /// **'Miamala ya Hivi Karibuni'**
  String get recentTransactions;

  /// No description provided for @regularCustomer.
  ///
  /// In sw, this message translates to:
  /// **'Mteja wa kawaida'**
  String get regularCustomer;

  /// No description provided for @yesterday.
  ///
  /// In sw, this message translates to:
  /// **'Jana'**
  String get yesterday;

  /// No description provided for @language.
  ///
  /// In sw, this message translates to:
  /// **'Lugha'**
  String get language;

  /// No description provided for @swahili.
  ///
  /// In sw, this message translates to:
  /// **'Kiswahili'**
  String get swahili;

  /// No description provided for @english.
  ///
  /// In sw, this message translates to:
  /// **'Kiingereza'**
  String get english;

  /// No description provided for @needHelp.
  ///
  /// In sw, this message translates to:
  /// **'Unahitaji msaada? Wasiliana na msimamizi'**
  String get needHelp;

  /// No description provided for @verifyEmail.
  ///
  /// In sw, this message translates to:
  /// **'Thibitisha Barua Pepe'**
  String get verifyEmail;

  /// No description provided for @businessDetails.
  ///
  /// In sw, this message translates to:
  /// **'Taarifa za Biashara'**
  String get businessDetails;

  /// No description provided for @companyName.
  ///
  /// In sw, this message translates to:
  /// **'Jina la kampuni'**
  String get companyName;

  /// No description provided for @phone.
  ///
  /// In sw, this message translates to:
  /// **'Simu'**
  String get phone;

  /// No description provided for @address.
  ///
  /// In sw, this message translates to:
  /// **'Anwani'**
  String get address;

  /// No description provided for @getStarted.
  ///
  /// In sw, this message translates to:
  /// **'Anza Kutumia'**
  String get getStarted;

  /// No description provided for @onboardingComplete.
  ///
  /// In sw, this message translates to:
  /// **'Umefanikiwa!'**
  String get onboardingComplete;

  /// No description provided for @lowStock.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa Zinazopungua'**
  String get lowStock;

  /// No description provided for @outOfStock.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa Zimeisha'**
  String get outOfStock;

  /// No description provided for @addExpense.
  ///
  /// In sw, this message translates to:
  /// **'Ongeza Matumizi'**
  String get addExpense;

  /// No description provided for @amount.
  ///
  /// In sw, this message translates to:
  /// **'Kiasi'**
  String get amount;

  /// No description provided for @description.
  ///
  /// In sw, this message translates to:
  /// **'Maelezo'**
  String get description;

  /// No description provided for @category.
  ///
  /// In sw, this message translates to:
  /// **'Aina'**
  String get category;

  /// No description provided for @date.
  ///
  /// In sw, this message translates to:
  /// **'Tarehe'**
  String get date;

  /// No description provided for @quantity.
  ///
  /// In sw, this message translates to:
  /// **'Idadi'**
  String get quantity;

  /// No description provided for @unitPrice.
  ///
  /// In sw, this message translates to:
  /// **'Bei ya kitu'**
  String get unitPrice;

  /// No description provided for @item.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa'**
  String get item;

  /// No description provided for @items.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa'**
  String get items;

  /// No description provided for @saleSaved.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo yamehifadhiwa!'**
  String get saleSaved;

  /// No description provided for @saleComplete.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo Yamekamilika!'**
  String get saleComplete;

  /// No description provided for @orderSaved.
  ///
  /// In sw, this message translates to:
  /// **'Oda imehifadhiwa!'**
  String get orderSaved;

  /// No description provided for @orderConverted.
  ///
  /// In sw, this message translates to:
  /// **'Oda imekamilishwa kuwa mauzo!'**
  String get orderConverted;

  /// No description provided for @orderCancelled.
  ///
  /// In sw, this message translates to:
  /// **'Oda imesitishwa'**
  String get orderCancelled;

  /// No description provided for @stockInsufficient.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa hazitoshi'**
  String get stockInsufficient;

  /// No description provided for @proformaFooter.
  ///
  /// In sw, this message translates to:
  /// **'Hii ni ankara ya proforma na si risiti. Malipo yanahitajika kuthibitisha oda hii.'**
  String get proformaFooter;

  /// No description provided for @billTo.
  ///
  /// In sw, this message translates to:
  /// **'Kwa'**
  String get billTo;

  /// No description provided for @validUntil.
  ///
  /// In sw, this message translates to:
  /// **'Inaisha'**
  String get validUntil;

  /// No description provided for @orderNumber.
  ///
  /// In sw, this message translates to:
  /// **'Nambari ya Oda'**
  String get orderNumber;

  /// No description provided for @transactionNumber.
  ///
  /// In sw, this message translates to:
  /// **'Nambari ya Muamala'**
  String get transactionNumber;

  /// No description provided for @viewReceipt.
  ///
  /// In sw, this message translates to:
  /// **'Tazama Risiti'**
  String get viewReceipt;

  /// No description provided for @viewProforma.
  ///
  /// In sw, this message translates to:
  /// **'Tazama Proforma'**
  String get viewProforma;

  /// No description provided for @print.
  ///
  /// In sw, this message translates to:
  /// **'Chapisha'**
  String get print;

  /// No description provided for @share.
  ///
  /// In sw, this message translates to:
  /// **'Shiriki'**
  String get share;

  /// No description provided for @home.
  ///
  /// In sw, this message translates to:
  /// **'Nyumbani'**
  String get home;

  /// No description provided for @sales.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo'**
  String get sales;

  /// No description provided for @overview.
  ///
  /// In sw, this message translates to:
  /// **'Muhtasari'**
  String get overview;

  /// No description provided for @details.
  ///
  /// In sw, this message translates to:
  /// **'Maelezo'**
  String get details;

  /// No description provided for @storeSettings.
  ///
  /// In sw, this message translates to:
  /// **'Mipangilio ya Duka'**
  String get storeSettings;

  /// No description provided for @changePin.
  ///
  /// In sw, this message translates to:
  /// **'Badilisha PIN'**
  String get changePin;

  /// No description provided for @changePassword.
  ///
  /// In sw, this message translates to:
  /// **'Badilisha Nywila'**
  String get changePassword;

  /// No description provided for @printerSetup.
  ///
  /// In sw, this message translates to:
  /// **'Printa'**
  String get printerSetup;

  /// No description provided for @notifications.
  ///
  /// In sw, this message translates to:
  /// **'Arifa'**
  String get notifications;

  /// No description provided for @helpSupport.
  ///
  /// In sw, this message translates to:
  /// **'Msaada'**
  String get helpSupport;

  /// No description provided for @about.
  ///
  /// In sw, this message translates to:
  /// **'Kuhusu'**
  String get about;

  /// No description provided for @logout.
  ///
  /// In sw, this message translates to:
  /// **'Ondoka'**
  String get logout;

  /// No description provided for @logoutConfirm.
  ///
  /// In sw, this message translates to:
  /// **'Una uhakika unataka kuondoka?'**
  String get logoutConfirm;

  /// No description provided for @business.
  ///
  /// In sw, this message translates to:
  /// **'Biashara'**
  String get business;

  /// No description provided for @account.
  ///
  /// In sw, this message translates to:
  /// **'Akaunti'**
  String get account;

  /// No description provided for @app.
  ///
  /// In sw, this message translates to:
  /// **'Programu'**
  String get app;

  /// No description provided for @support.
  ///
  /// In sw, this message translates to:
  /// **'Msaada'**
  String get support;

  /// No description provided for @quickActions.
  ///
  /// In sw, this message translates to:
  /// **'Vitendo vya Haraka'**
  String get quickActions;

  /// No description provided for @stock.
  ///
  /// In sw, this message translates to:
  /// **'Hifadhi'**
  String get stock;

  /// No description provided for @reports.
  ///
  /// In sw, this message translates to:
  /// **'Ripoti'**
  String get reports;

  /// No description provided for @analytics.
  ///
  /// In sw, this message translates to:
  /// **'Uchambuzi'**
  String get analytics;

  /// No description provided for @management.
  ///
  /// In sw, this message translates to:
  /// **'Usimamizi'**
  String get management;

  /// No description provided for @products.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa'**
  String get products;

  /// No description provided for @categories.
  ///
  /// In sw, this message translates to:
  /// **'Makundi'**
  String get categories;

  /// No description provided for @expenseCategories.
  ///
  /// In sw, this message translates to:
  /// **'Aina za Matumizi'**
  String get expenseCategories;

  /// No description provided for @profitAnalysis.
  ///
  /// In sw, this message translates to:
  /// **'Uchambuzi wa Faida'**
  String get profitAnalysis;

  /// No description provided for @byBranch.
  ///
  /// In sw, this message translates to:
  /// **'Kwa Tawi'**
  String get byBranch;

  /// No description provided for @trends.
  ///
  /// In sw, this message translates to:
  /// **'Mwelekeo'**
  String get trends;

  /// No description provided for @users.
  ///
  /// In sw, this message translates to:
  /// **'Watumiaji'**
  String get users;

  /// No description provided for @branches.
  ///
  /// In sw, this message translates to:
  /// **'Matawi'**
  String get branches;

  /// No description provided for @documentation.
  ///
  /// In sw, this message translates to:
  /// **'Mwongozo'**
  String get documentation;

  /// No description provided for @addCustomerInfo.
  ///
  /// In sw, this message translates to:
  /// **'Ongeza taarifa za mteja (hiari)'**
  String get addCustomerInfo;

  /// No description provided for @hideCustomerInfo.
  ///
  /// In sw, this message translates to:
  /// **'Ficha taarifa za mteja'**
  String get hideCustomerInfo;

  /// No description provided for @enterQuantity.
  ///
  /// In sw, this message translates to:
  /// **'Weka Idadi'**
  String get enterQuantity;

  /// No description provided for @ok.
  ///
  /// In sw, this message translates to:
  /// **'Sawa'**
  String get ok;

  /// No description provided for @voidReason.
  ///
  /// In sw, this message translates to:
  /// **'Sababu ya kufuta'**
  String get voidReason;

  /// No description provided for @voidReasonHint.
  ///
  /// In sw, this message translates to:
  /// **'Andika sababu ya kufuta...'**
  String get voidReasonHint;

  /// No description provided for @voidConfirm.
  ///
  /// In sw, this message translates to:
  /// **'Una uhakika unataka kufuta muamala huu?'**
  String get voidConfirm;

  /// No description provided for @transactionVoided.
  ///
  /// In sw, this message translates to:
  /// **'Muamala umefutwa'**
  String get transactionVoided;

  /// No description provided for @failedToLoad.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kupakia'**
  String get failedToLoad;

  /// No description provided for @failedToProcess.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kuchakata'**
  String get failedToProcess;

  /// No description provided for @thankYou.
  ///
  /// In sw, this message translates to:
  /// **'Asante kwa ununuzi wako!'**
  String get thankYou;

  /// No description provided for @welcomeAgain.
  ///
  /// In sw, this message translates to:
  /// **'Karibu tena'**
  String get welcomeAgain;

  /// No description provided for @poweredBy.
  ///
  /// In sw, this message translates to:
  /// **'Powered by Sasampa POS'**
  String get poweredBy;

  /// No description provided for @customerNameRequired.
  ///
  /// In sw, this message translates to:
  /// **'Jina la mteja linahitajika kwa oda'**
  String get customerNameRequired;

  /// No description provided for @cancelReason.
  ///
  /// In sw, this message translates to:
  /// **'Sababu ya kughairi'**
  String get cancelReason;

  /// No description provided for @cancelReasonHint.
  ///
  /// In sw, this message translates to:
  /// **'Andika sababu ya kughairi...'**
  String get cancelReasonHint;

  /// No description provided for @version.
  ///
  /// In sw, this message translates to:
  /// **'Toleo'**
  String get version;

  /// No description provided for @web.
  ///
  /// In sw, this message translates to:
  /// **'WAVUTI'**
  String get web;

  /// No description provided for @tinNumber.
  ///
  /// In sw, this message translates to:
  /// **'Namba ya TIN'**
  String get tinNumber;

  /// No description provided for @deviceName.
  ///
  /// In sw, this message translates to:
  /// **'Jina la kifaa'**
  String get deviceName;

  /// No description provided for @pointOfSale.
  ///
  /// In sw, this message translates to:
  /// **'Sehemu ya Mauzo'**
  String get pointOfSale;

  /// No description provided for @searchProducts.
  ///
  /// In sw, this message translates to:
  /// **'Tafuta bidhaa...'**
  String get searchProducts;

  /// No description provided for @productOutOfStock.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa imeisha'**
  String get productOutOfStock;

  /// No description provided for @cartEmpty.
  ///
  /// In sw, this message translates to:
  /// **'Kikapu ni tupu'**
  String get cartEmpty;

  /// No description provided for @noProductsFound.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna bidhaa zilizopatikana'**
  String get noProductsFound;

  /// No description provided for @noProductsAvailable.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna bidhaa zinazopatikana'**
  String get noProductsAvailable;

  /// No description provided for @productNotFoundBarcode.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa haijapatikana kwa msimbo'**
  String get productNotFoundBarcode;

  /// No description provided for @transaction.
  ///
  /// In sw, this message translates to:
  /// **'Muamala'**
  String get transaction;

  /// No description provided for @payment.
  ///
  /// In sw, this message translates to:
  /// **'Malipo'**
  String get payment;

  /// No description provided for @cashier.
  ///
  /// In sw, this message translates to:
  /// **'Karani'**
  String get cashier;

  /// No description provided for @customer.
  ///
  /// In sw, this message translates to:
  /// **'Mteja'**
  String get customer;

  /// No description provided for @branch.
  ///
  /// In sw, this message translates to:
  /// **'Tawi'**
  String get branch;

  /// No description provided for @pleaseEnterReason.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali andika sababu'**
  String get pleaseEnterReason;

  /// No description provided for @failedToVoid.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kufuta muamala'**
  String get failedToVoid;

  /// No description provided for @failedToPrint.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kuchapisha risiti'**
  String get failedToPrint;

  /// No description provided for @failedToShare.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kushiriki risiti'**
  String get failedToShare;

  /// No description provided for @todayExpenses.
  ///
  /// In sw, this message translates to:
  /// **'Matumizi ya Leo'**
  String get todayExpenses;

  /// No description provided for @thisWeekExpenses.
  ///
  /// In sw, this message translates to:
  /// **'Matumizi ya Wiki'**
  String get thisWeekExpenses;

  /// No description provided for @thisMonthExpenses.
  ///
  /// In sw, this message translates to:
  /// **'Matumizi ya Mwezi'**
  String get thisMonthExpenses;

  /// No description provided for @records.
  ///
  /// In sw, this message translates to:
  /// **'Rekodi'**
  String get records;

  /// No description provided for @customRange.
  ///
  /// In sw, this message translates to:
  /// **'Maalum'**
  String get customRange;

  /// No description provided for @noExpensesRecorded.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna matumizi yaliyorekodiwa'**
  String get noExpensesRecorded;

  /// No description provided for @expenseDeleted.
  ///
  /// In sw, this message translates to:
  /// **'Matumizi yamefutwa'**
  String get expenseDeleted;

  /// No description provided for @deleteExpense.
  ///
  /// In sw, this message translates to:
  /// **'Futa Matumizi'**
  String get deleteExpense;

  /// No description provided for @deleteExpenseConfirm.
  ///
  /// In sw, this message translates to:
  /// **'Una uhakika unataka kufuta matumizi haya?'**
  String get deleteExpenseConfirm;

  /// No description provided for @totalValue.
  ///
  /// In sw, this message translates to:
  /// **'Thamani Yote'**
  String get totalValue;

  /// No description provided for @totalItems.
  ///
  /// In sw, this message translates to:
  /// **'Vitu Vyote'**
  String get totalItems;

  /// No description provided for @adjustStock.
  ///
  /// In sw, this message translates to:
  /// **'Rekebisha Hifadhi'**
  String get adjustStock;

  /// No description provided for @currentStock.
  ///
  /// In sw, this message translates to:
  /// **'Hifadhi ya Sasa'**
  String get currentStock;

  /// No description provided for @addStock.
  ///
  /// In sw, this message translates to:
  /// **'Ongeza Hifadhi'**
  String get addStock;

  /// No description provided for @removeStock.
  ///
  /// In sw, this message translates to:
  /// **'Punguza Hifadhi'**
  String get removeStock;

  /// No description provided for @saveAdjustment.
  ///
  /// In sw, this message translates to:
  /// **'Hifadhi Marekebisho'**
  String get saveAdjustment;

  /// No description provided for @stockAdjusted.
  ///
  /// In sw, this message translates to:
  /// **'Hifadhi imerekebishwa'**
  String get stockAdjusted;

  /// No description provided for @inStock.
  ///
  /// In sw, this message translates to:
  /// **'Ipo'**
  String get inStock;

  /// No description provided for @noLowStockItems.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna bidhaa zinazopungua'**
  String get noLowStockItems;

  /// No description provided for @noOutOfStockItems.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna bidhaa zilizoisha'**
  String get noOutOfStockItems;

  /// No description provided for @reasonStockIn.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa Zimeingia'**
  String get reasonStockIn;

  /// No description provided for @reasonStockOut.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa Zimetoka'**
  String get reasonStockOut;

  /// No description provided for @reasonDamaged.
  ///
  /// In sw, this message translates to:
  /// **'Zimeharibika'**
  String get reasonDamaged;

  /// No description provided for @reasonLost.
  ///
  /// In sw, this message translates to:
  /// **'Zimepotea'**
  String get reasonLost;

  /// No description provided for @reasonReturned.
  ///
  /// In sw, this message translates to:
  /// **'Zimerudishwa'**
  String get reasonReturned;

  /// No description provided for @reasonCorrection.
  ///
  /// In sw, this message translates to:
  /// **'Marekebisho'**
  String get reasonCorrection;

  /// No description provided for @reasonOther.
  ///
  /// In sw, this message translates to:
  /// **'Nyingine'**
  String get reasonOther;

  /// No description provided for @pointCameraAtBarcode.
  ///
  /// In sw, this message translates to:
  /// **'Elekeza kamera kwenye msimbo'**
  String get pointCameraAtBarcode;

  /// No description provided for @time.
  ///
  /// In sw, this message translates to:
  /// **'Saa'**
  String get time;

  /// No description provided for @phoneNumber.
  ///
  /// In sw, this message translates to:
  /// **'Namba ya simu'**
  String get phoneNumber;

  /// No description provided for @signInSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Ingia kwenye akaunti yako'**
  String get signInSubtitle;

  /// No description provided for @enterPinSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Weka PIN yako kuendelea'**
  String get enterPinSubtitle;

  /// No description provided for @signUpSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Jisajili kuanza kutumia Sasampa POS'**
  String get signUpSubtitle;

  /// No description provided for @pin.
  ///
  /// In sw, this message translates to:
  /// **'PIN'**
  String get pin;

  /// No description provided for @enterPin.
  ///
  /// In sw, this message translates to:
  /// **'Weka PIN yako ya tarakimu 4'**
  String get enterPin;

  /// No description provided for @manageOrders.
  ///
  /// In sw, this message translates to:
  /// **'Simamia oda na proforma'**
  String get manageOrders;

  /// No description provided for @manageProducts.
  ///
  /// In sw, this message translates to:
  /// **'Simamia bidhaa'**
  String get manageProducts;

  /// No description provided for @productCategories.
  ///
  /// In sw, this message translates to:
  /// **'Makundi ya bidhaa'**
  String get productCategories;

  /// No description provided for @stockLevels.
  ///
  /// In sw, this message translates to:
  /// **'Viwango vya hifadhi na marekebisho'**
  String get stockLevels;

  /// No description provided for @trackCosts.
  ///
  /// In sw, this message translates to:
  /// **'Fuatilia gharama za uendeshaji'**
  String get trackCosts;

  /// No description provided for @organizeExpenses.
  ///
  /// In sw, this message translates to:
  /// **'Panga aina za matumizi'**
  String get organizeExpenses;

  /// No description provided for @viewAllSales.
  ///
  /// In sw, this message translates to:
  /// **'Tazama mauzo yote'**
  String get viewAllSales;

  /// No description provided for @salesReports.
  ///
  /// In sw, this message translates to:
  /// **'Ripoti za mauzo, bidhaa na faida'**
  String get salesReports;

  /// No description provided for @revenueProfit.
  ///
  /// In sw, this message translates to:
  /// **'Mapato na faida'**
  String get revenueProfit;

  /// No description provided for @compareBranch.
  ///
  /// In sw, this message translates to:
  /// **'Linganisha utendaji wa matawi'**
  String get compareBranch;

  /// No description provided for @performanceTrends.
  ///
  /// In sw, this message translates to:
  /// **'Utendaji kwa wakati'**
  String get performanceTrends;

  /// No description provided for @manageStaff.
  ///
  /// In sw, this message translates to:
  /// **'Simamia akaunti za wafanyakazi'**
  String get manageStaff;

  /// No description provided for @manageLocations.
  ///
  /// In sw, this message translates to:
  /// **'Simamia maeneo ya biashara'**
  String get manageLocations;

  /// No description provided for @guidesHelp.
  ///
  /// In sw, this message translates to:
  /// **'Miongozo na makala ya msaada'**
  String get guidesHelp;

  /// No description provided for @help.
  ///
  /// In sw, this message translates to:
  /// **'Msaada'**
  String get help;

  /// No description provided for @summary.
  ///
  /// In sw, this message translates to:
  /// **'Muhtasari'**
  String get summary;

  /// No description provided for @unknownProduct.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa isiyojulikana'**
  String get unknownProduct;

  /// No description provided for @addedToCart.
  ///
  /// In sw, this message translates to:
  /// **'imeongezwa kwenye kikapu'**
  String get addedToCart;

  /// No description provided for @vat.
  ///
  /// In sw, this message translates to:
  /// **'VAT'**
  String get vat;

  /// No description provided for @verifyEmailSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Tumetuma kiungo cha uthibitisho kwenye barua pepe yako. Tafadhali angalia na ubonyeze kiungo kuendelea.'**
  String get verifyEmailSubtitle;

  /// No description provided for @resendVerificationEmail.
  ///
  /// In sw, this message translates to:
  /// **'Tuma tena barua pepe ya uthibitisho'**
  String get resendVerificationEmail;

  /// No description provided for @sending.
  ///
  /// In sw, this message translates to:
  /// **'Inatuma...'**
  String get sending;

  /// No description provided for @verificationEmailSent.
  ///
  /// In sw, this message translates to:
  /// **'Barua pepe ya uthibitisho imetumwa!'**
  String get verificationEmailSent;

  /// No description provided for @failedToSend.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kutuma. Tafadhali jaribu tena.'**
  String get failedToSend;

  /// No description provided for @iveVerifiedEmail.
  ///
  /// In sw, this message translates to:
  /// **'Nimethibitisha barua pepe yangu'**
  String get iveVerifiedEmail;

  /// No description provided for @waitingForVerification.
  ///
  /// In sw, this message translates to:
  /// **'Inasubiri uthibitisho...'**
  String get waitingForVerification;

  /// No description provided for @businessDetailsSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Tuambie kuhusu biashara yako'**
  String get businessDetailsSubtitle;

  /// No description provided for @continueBtn.
  ///
  /// In sw, this message translates to:
  /// **'Endelea'**
  String get continueBtn;

  /// No description provided for @phoneOptional.
  ///
  /// In sw, this message translates to:
  /// **'Namba ya simu (hiari)'**
  String get phoneOptional;

  /// No description provided for @addressOptional.
  ///
  /// In sw, this message translates to:
  /// **'Anwani (hiari)'**
  String get addressOptional;

  /// No description provided for @allSet.
  ///
  /// In sw, this message translates to:
  /// **'Uko Tayari!'**
  String get allSet;

  /// No description provided for @allSetSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Akaunti yako imeanzishwa vizuri. Sasa unaweza kuanza kutumia Sasampa POS kusimamia biashara yako.'**
  String get allSetSubtitle;

  /// No description provided for @failedToComplete.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kukamilisha. Tafadhali jaribu tena.'**
  String get failedToComplete;

  /// No description provided for @customizeDashboard.
  ///
  /// In sw, this message translates to:
  /// **'Binafsisha Dashibodi'**
  String get customizeDashboard;

  /// No description provided for @customizeDashboardDesc.
  ///
  /// In sw, this message translates to:
  /// **'Mpangilio, vijisehemu na ukurasa wa kuanzia'**
  String get customizeDashboardDesc;

  /// No description provided for @dashboardLayout.
  ///
  /// In sw, this message translates to:
  /// **'Mpangilio wa Dashibodi'**
  String get dashboardLayout;

  /// No description provided for @layoutClassic.
  ///
  /// In sw, this message translates to:
  /// **'Kawaida'**
  String get layoutClassic;

  /// No description provided for @layoutAnalytics.
  ///
  /// In sw, this message translates to:
  /// **'Uchambuzi'**
  String get layoutAnalytics;

  /// No description provided for @layoutCompact.
  ///
  /// In sw, this message translates to:
  /// **'Fupi'**
  String get layoutCompact;

  /// No description provided for @dashboardWidgets.
  ///
  /// In sw, this message translates to:
  /// **'Vijisehemu vya Dashibodi'**
  String get dashboardWidgets;

  /// No description provided for @defaultLandingPage.
  ///
  /// In sw, this message translates to:
  /// **'Ukurasa wa Kuanzia'**
  String get defaultLandingPage;

  /// No description provided for @resetToDefault.
  ///
  /// In sw, this message translates to:
  /// **'Rudisha Mipangilio'**
  String get resetToDefault;

  /// No description provided for @todayStatsWidget.
  ///
  /// In sw, this message translates to:
  /// **'Takwimu za Leo'**
  String get todayStatsWidget;

  /// No description provided for @quickActionsWidget.
  ///
  /// In sw, this message translates to:
  /// **'Vitendo vya Haraka'**
  String get quickActionsWidget;

  /// No description provided for @lowStockAlertWidget.
  ///
  /// In sw, this message translates to:
  /// **'Tahadhari ya Bidhaa'**
  String get lowStockAlertWidget;

  /// No description provided for @recentTransactionsWidget.
  ///
  /// In sw, this message translates to:
  /// **'Miamala ya Karibuni'**
  String get recentTransactionsWidget;

  /// No description provided for @weeklySummaryWidget.
  ///
  /// In sw, this message translates to:
  /// **'Muhtasari wa Wiki'**
  String get weeklySummaryWidget;

  /// No description provided for @topProductsWidget.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa Bora'**
  String get topProductsWidget;

  /// No description provided for @dragToReorder.
  ///
  /// In sw, this message translates to:
  /// **'Buruta kupanga upya'**
  String get dragToReorder;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['en', 'sw'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'en':
      return AppLocalizationsEn();
    case 'sw':
      return AppLocalizationsSw();
  }

  throw FlutterError(
    'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
    'an issue with the localizations generation tool. Please file an issue '
    'on GitHub with a reproducible sample app and the gen-l10n configuration '
    'that was used.',
  );
}
