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
  /// **'Bei ya Kitu (TZS)'**
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

  /// No description provided for @enterYourEmail.
  ///
  /// In sw, this message translates to:
  /// **'Weka barua pepe yako'**
  String get enterYourEmail;

  /// No description provided for @pleaseEnterEmail.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali weka barua pepe yako'**
  String get pleaseEnterEmail;

  /// No description provided for @pleaseEnterValidEmail.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali weka barua pepe sahihi'**
  String get pleaseEnterValidEmail;

  /// No description provided for @enterYourPassword.
  ///
  /// In sw, this message translates to:
  /// **'Weka nywila yako'**
  String get enterYourPassword;

  /// No description provided for @pleaseEnterPassword.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali weka nywila yako'**
  String get pleaseEnterPassword;

  /// No description provided for @enterYourFullName.
  ///
  /// In sw, this message translates to:
  /// **'Weka jina lako kamili'**
  String get enterYourFullName;

  /// No description provided for @pleaseEnterName.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali weka jina lako'**
  String get pleaseEnterName;

  /// No description provided for @minimumEightCharacters.
  ///
  /// In sw, this message translates to:
  /// **'Herufi 8 au zaidi'**
  String get minimumEightCharacters;

  /// No description provided for @reenterPassword.
  ///
  /// In sw, this message translates to:
  /// **'Weka tena nywila yako'**
  String get reenterPassword;

  /// No description provided for @emailAlreadyRegistered.
  ///
  /// In sw, this message translates to:
  /// **'Barua pepe hii imesajiliwa tayari. Tafadhali ingia badala yake.'**
  String get emailAlreadyRegistered;

  /// No description provided for @registrationFailed.
  ///
  /// In sw, this message translates to:
  /// **'Usajili umeshindwa. Tafadhali jaribu tena.'**
  String get registrationFailed;

  /// No description provided for @enterBusinessName.
  ///
  /// In sw, this message translates to:
  /// **'Weka jina la biashara yako'**
  String get enterBusinessName;

  /// No description provided for @pleaseEnterBusinessName.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali weka jina la biashara yako'**
  String get pleaseEnterBusinessName;

  /// No description provided for @pleaseEnterPin.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali weka PIN yako'**
  String get pleaseEnterPin;

  /// No description provided for @pinMustBeFourDigits.
  ///
  /// In sw, this message translates to:
  /// **'PIN lazima iwe tarakimu 4'**
  String get pinMustBeFourDigits;

  /// No description provided for @productsNeedRestocking.
  ///
  /// In sw, this message translates to:
  /// **'bidhaa zinahitaji kujazwa'**
  String get productsNeedRestocking;

  /// No description provided for @noWeeklyData.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna data ya wiki'**
  String get noWeeklyData;

  /// No description provided for @noProductData.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna data ya bidhaa'**
  String get noProductData;

  /// No description provided for @sold.
  ///
  /// In sw, this message translates to:
  /// **'zimeuzwa'**
  String get sold;

  /// No description provided for @failedToLoadDashboard.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kupakia dashibodi'**
  String get failedToLoadDashboard;

  /// No description provided for @hello.
  ///
  /// In sw, this message translates to:
  /// **'Habari'**
  String get hello;

  /// No description provided for @mon.
  ///
  /// In sw, this message translates to:
  /// **'Jtt'**
  String get mon;

  /// No description provided for @tue.
  ///
  /// In sw, this message translates to:
  /// **'Jnn'**
  String get tue;

  /// No description provided for @wed.
  ///
  /// In sw, this message translates to:
  /// **'Jtn'**
  String get wed;

  /// No description provided for @thu.
  ///
  /// In sw, this message translates to:
  /// **'Alh'**
  String get thu;

  /// No description provided for @fri.
  ///
  /// In sw, this message translates to:
  /// **'Ijm'**
  String get fri;

  /// No description provided for @sat.
  ///
  /// In sw, this message translates to:
  /// **'Jmt'**
  String get sat;

  /// No description provided for @sun.
  ///
  /// In sw, this message translates to:
  /// **'Jpi'**
  String get sun;

  /// No description provided for @currentPin.
  ///
  /// In sw, this message translates to:
  /// **'PIN ya Sasa'**
  String get currentPin;

  /// No description provided for @newPin.
  ///
  /// In sw, this message translates to:
  /// **'PIN Mpya (tarakimu 4-6)'**
  String get newPin;

  /// No description provided for @confirmNewPin.
  ///
  /// In sw, this message translates to:
  /// **'Thibitisha PIN Mpya'**
  String get confirmNewPin;

  /// No description provided for @pinTooShort.
  ///
  /// In sw, this message translates to:
  /// **'PIN lazima iwe tarakimu 4 au zaidi'**
  String get pinTooShort;

  /// No description provided for @pinsDoNotMatch.
  ///
  /// In sw, this message translates to:
  /// **'PIN hazilingani'**
  String get pinsDoNotMatch;

  /// No description provided for @pinChangedSuccessfully.
  ///
  /// In sw, this message translates to:
  /// **'PIN imebadilishwa'**
  String get pinChangedSuccessfully;

  /// No description provided for @currentPassword.
  ///
  /// In sw, this message translates to:
  /// **'Nywila ya Sasa'**
  String get currentPassword;

  /// No description provided for @newPassword.
  ///
  /// In sw, this message translates to:
  /// **'Nywila Mpya'**
  String get newPassword;

  /// No description provided for @confirmNewPassword.
  ///
  /// In sw, this message translates to:
  /// **'Thibitisha Nywila Mpya'**
  String get confirmNewPassword;

  /// No description provided for @passwordTooShort.
  ///
  /// In sw, this message translates to:
  /// **'Nywila lazima iwe herufi 8 au zaidi'**
  String get passwordTooShort;

  /// No description provided for @passwordsDoNotMatch.
  ///
  /// In sw, this message translates to:
  /// **'Nywila hazilingani'**
  String get passwordsDoNotMatch;

  /// No description provided for @passwordChangedSuccessfully.
  ///
  /// In sw, this message translates to:
  /// **'Nywila imebadilishwa'**
  String get passwordChangedSuccessfully;

  /// No description provided for @changeAction.
  ///
  /// In sw, this message translates to:
  /// **'Badilisha'**
  String get changeAction;

  /// No description provided for @receiptPrintingOptions.
  ///
  /// In sw, this message translates to:
  /// **'Chaguo za Kuchapisha Risiti:'**
  String get receiptPrintingOptions;

  /// No description provided for @airprint.
  ///
  /// In sw, this message translates to:
  /// **'AirPrint'**
  String get airprint;

  /// No description provided for @printToAnyPrinter.
  ///
  /// In sw, this message translates to:
  /// **'Chapisha kwa printa yoyote ya AirPrint'**
  String get printToAnyPrinter;

  /// No description provided for @testReceipt.
  ///
  /// In sw, this message translates to:
  /// **'Risiti ya Majaribio'**
  String get testReceipt;

  /// No description provided for @shareAsPdf.
  ///
  /// In sw, this message translates to:
  /// **'Shiriki kama PDF'**
  String get shareAsPdf;

  /// No description provided for @saveOrShareReceipts.
  ///
  /// In sw, this message translates to:
  /// **'Hifadhi au shiriki risiti kama PDF'**
  String get saveOrShareReceipts;

  /// No description provided for @printerTip.
  ///
  /// In sw, this message translates to:
  /// **'Tumia vitufe vya Chapisha au Shiriki kwenye risiti kuchapisha moja kwa moja.'**
  String get printerTip;

  /// No description provided for @manageNotificationPreferences.
  ///
  /// In sw, this message translates to:
  /// **'Dhibiti mapendeleo yako ya arifa:'**
  String get manageNotificationPreferences;

  /// No description provided for @openSystemSettings.
  ///
  /// In sw, this message translates to:
  /// **'Fungua Mipangilio ya Simu'**
  String get openSystemSettings;

  /// No description provided for @configureNotifications.
  ///
  /// In sw, this message translates to:
  /// **'Weka arifa za programu'**
  String get configureNotifications;

  /// No description provided for @notificationsInclude.
  ///
  /// In sw, this message translates to:
  /// **'Arifa zinajumuisha:'**
  String get notificationsInclude;

  /// No description provided for @lowStockAlerts.
  ///
  /// In sw, this message translates to:
  /// **'Tahadhari za bidhaa chache'**
  String get lowStockAlerts;

  /// No description provided for @newOrderNotifications.
  ///
  /// In sw, this message translates to:
  /// **'Arifa za oda mpya'**
  String get newOrderNotifications;

  /// No description provided for @systemUpdates.
  ///
  /// In sw, this message translates to:
  /// **'Masasisho ya mfumo'**
  String get systemUpdates;

  /// No description provided for @forAssistanceContact.
  ///
  /// In sw, this message translates to:
  /// **'Kwa msaada, wasiliana na:'**
  String get forAssistanceContact;

  /// No description provided for @aboutSasampa.
  ///
  /// In sw, this message translates to:
  /// **'Kuhusu Sasampa'**
  String get aboutSasampa;

  /// No description provided for @modernPosSystem.
  ///
  /// In sw, this message translates to:
  /// **'Mfumo wa kisasa wa mauzo kwa biashara yako.'**
  String get modernPosSystem;

  /// No description provided for @failedToLogout.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kutoka. Tafadhali jaribu tena.'**
  String get failedToLogout;

  /// No description provided for @noInternetConnection.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna mtandao'**
  String get noInternetConnection;

  /// No description provided for @editExpense.
  ///
  /// In sw, this message translates to:
  /// **'Hariri Matumizi'**
  String get editExpense;

  /// No description provided for @expenseUpdated.
  ///
  /// In sw, this message translates to:
  /// **'Matumizi yamesasishwa'**
  String get expenseUpdated;

  /// No description provided for @expenseRecorded.
  ///
  /// In sw, this message translates to:
  /// **'Matumizi yamerekodiwa'**
  String get expenseRecorded;

  /// No description provided for @addCategory.
  ///
  /// In sw, this message translates to:
  /// **'Ongeza Aina'**
  String get addCategory;

  /// No description provided for @categoryName.
  ///
  /// In sw, this message translates to:
  /// **'Jina la Aina'**
  String get categoryName;

  /// No description provided for @selectCategory.
  ///
  /// In sw, this message translates to:
  /// **'Chagua'**
  String get selectCategory;

  /// No description provided for @unitPriceTzs.
  ///
  /// In sw, this message translates to:
  /// **'Bei ya Kitu (TZS)'**
  String get unitPriceTzs;

  /// No description provided for @supplier.
  ///
  /// In sw, this message translates to:
  /// **'Msambazaji'**
  String get supplier;

  /// No description provided for @vendorOrSupplierName.
  ///
  /// In sw, this message translates to:
  /// **'Jina la msambazaji'**
  String get vendorOrSupplierName;

  /// No description provided for @referenceNumber.
  ///
  /// In sw, this message translates to:
  /// **'Nambari ya Rejea'**
  String get referenceNumber;

  /// No description provided for @receiptOrInvoiceNumber.
  ///
  /// In sw, this message translates to:
  /// **'Nambari ya risiti au ankara'**
  String get receiptOrInvoiceNumber;

  /// No description provided for @additionalNotes.
  ///
  /// In sw, this message translates to:
  /// **'Maelezo zaidi...'**
  String get additionalNotes;

  /// No description provided for @updateExpense.
  ///
  /// In sw, this message translates to:
  /// **'Sasisha Matumizi'**
  String get updateExpense;

  /// No description provided for @saveExpense.
  ///
  /// In sw, this message translates to:
  /// **'Hifadhi Matumizi'**
  String get saveExpense;

  /// No description provided for @expenseSummary.
  ///
  /// In sw, this message translates to:
  /// **'Muhtasari wa Matumizi'**
  String get expenseSummary;

  /// No description provided for @period.
  ///
  /// In sw, this message translates to:
  /// **'Kipindi'**
  String get period;

  /// No description provided for @totalExpensesLabel.
  ///
  /// In sw, this message translates to:
  /// **'Jumla ya Matumizi'**
  String get totalExpensesLabel;

  /// No description provided for @byCategory.
  ///
  /// In sw, this message translates to:
  /// **'KWA AINA'**
  String get byCategory;

  /// No description provided for @byPaymentMethod.
  ///
  /// In sw, this message translates to:
  /// **'KWA NJIA YA MALIPO'**
  String get byPaymentMethod;

  /// No description provided for @noExpensesInPeriod.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna matumizi katika kipindi hiki'**
  String get noExpensesInPeriod;

  /// No description provided for @unit.
  ///
  /// In sw, this message translates to:
  /// **'Kipimo'**
  String get unit;

  /// No description provided for @selectUnit.
  ///
  /// In sw, this message translates to:
  /// **'Chagua Kipimo'**
  String get selectUnit;

  /// No description provided for @notes.
  ///
  /// In sw, this message translates to:
  /// **'Maelezo'**
  String get notes;

  /// No description provided for @profitBreakdown.
  ///
  /// In sw, this message translates to:
  /// **'Uchambuzi wa Faida'**
  String get profitBreakdown;

  /// No description provided for @profitBreakdownWidget.
  ///
  /// In sw, this message translates to:
  /// **'Uchambuzi wa Faida'**
  String get profitBreakdownWidget;

  /// No description provided for @revenue.
  ///
  /// In sw, this message translates to:
  /// **'Mapato'**
  String get revenue;

  /// No description provided for @costOfGoods.
  ///
  /// In sw, this message translates to:
  /// **'Gharama za Bidhaa'**
  String get costOfGoods;

  /// No description provided for @grossProfit.
  ///
  /// In sw, this message translates to:
  /// **'Faida Ghafi'**
  String get grossProfit;

  /// No description provided for @netProfit.
  ///
  /// In sw, this message translates to:
  /// **'Faida Halisi'**
  String get netProfit;

  /// No description provided for @margin.
  ///
  /// In sw, this message translates to:
  /// **'faida'**
  String get margin;

  /// No description provided for @pleaseEnterPhoneNumber.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali weka namba ya simu yako'**
  String get pleaseEnterPhoneNumber;

  /// No description provided for @invalidPhoneNumber.
  ///
  /// In sw, this message translates to:
  /// **'Weka namba sahihi ya simu na nambari ya nchi (mfano +255712345678)'**
  String get invalidPhoneNumber;

  /// No description provided for @editEmail.
  ///
  /// In sw, this message translates to:
  /// **'Badilisha Barua Pepe'**
  String get editEmail;

  /// No description provided for @emailUpdatedVerificationSent.
  ///
  /// In sw, this message translates to:
  /// **'Barua pepe imebadilishwa. Barua pepe mpya ya uthibitisho imetumwa!'**
  String get emailUpdatedVerificationSent;

  /// No description provided for @customers.
  ///
  /// In sw, this message translates to:
  /// **'Wateja'**
  String get customers;

  /// No description provided for @addCustomer.
  ///
  /// In sw, this message translates to:
  /// **'Ongeza Mteja'**
  String get addCustomer;

  /// No description provided for @editCustomer.
  ///
  /// In sw, this message translates to:
  /// **'Hariri Mteja'**
  String get editCustomer;

  /// No description provided for @customerDetails.
  ///
  /// In sw, this message translates to:
  /// **'Taarifa za Mteja'**
  String get customerDetails;

  /// No description provided for @selectCustomer.
  ///
  /// In sw, this message translates to:
  /// **'Chagua Mteja'**
  String get selectCustomer;

  /// No description provided for @searchCustomers.
  ///
  /// In sw, this message translates to:
  /// **'Tafuta wateja...'**
  String get searchCustomers;

  /// No description provided for @noCustomers.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna wateja'**
  String get noCustomers;

  /// No description provided for @manageCustomers.
  ///
  /// In sw, this message translates to:
  /// **'Simamia wateja na mikopo'**
  String get manageCustomers;

  /// No description provided for @customerCreated.
  ///
  /// In sw, this message translates to:
  /// **'Mteja ameongezwa'**
  String get customerCreated;

  /// No description provided for @customerUpdated.
  ///
  /// In sw, this message translates to:
  /// **'Mteja amesasishwa'**
  String get customerUpdated;

  /// No description provided for @creditLimit.
  ///
  /// In sw, this message translates to:
  /// **'Kikomo cha Mkopo'**
  String get creditLimit;

  /// No description provided for @creditBalance.
  ///
  /// In sw, this message translates to:
  /// **'Deni la Mkopo'**
  String get creditBalance;

  /// No description provided for @availableCredit.
  ///
  /// In sw, this message translates to:
  /// **'Mkopo Unapatikana'**
  String get availableCredit;

  /// No description provided for @credit.
  ///
  /// In sw, this message translates to:
  /// **'Mkopo'**
  String get credit;

  /// No description provided for @creditSale.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo ya Mkopo'**
  String get creditSale;

  /// No description provided for @insufficientCredit.
  ///
  /// In sw, this message translates to:
  /// **'Mkopo hautoshi'**
  String get insufficientCredit;

  /// No description provided for @recordPayment.
  ///
  /// In sw, this message translates to:
  /// **'Rekodi Malipo'**
  String get recordPayment;

  /// No description provided for @paymentRecorded.
  ///
  /// In sw, this message translates to:
  /// **'Malipo yamerekodiwa'**
  String get paymentRecorded;

  /// No description provided for @creditHistory.
  ///
  /// In sw, this message translates to:
  /// **'Historia ya Mkopo'**
  String get creditHistory;

  /// No description provided for @noCreditHistory.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna historia ya mkopo'**
  String get noCreditHistory;

  /// No description provided for @purchaseHistory.
  ///
  /// In sw, this message translates to:
  /// **'Historia ya Manunuzi'**
  String get purchaseHistory;

  /// No description provided for @bluetoothPrinter.
  ///
  /// In sw, this message translates to:
  /// **'Printa ya Bluetooth'**
  String get bluetoothPrinter;

  /// No description provided for @scanForPrinters.
  ///
  /// In sw, this message translates to:
  /// **'Tafuta Printa'**
  String get scanForPrinters;

  /// No description provided for @scanning.
  ///
  /// In sw, this message translates to:
  /// **'Inatafuta...'**
  String get scanning;

  /// No description provided for @noDevicesFound.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna vifaa vilivyopatikana'**
  String get noDevicesFound;

  /// No description provided for @connectPrinter.
  ///
  /// In sw, this message translates to:
  /// **'Unganisha Printa'**
  String get connectPrinter;

  /// No description provided for @disconnectPrinter.
  ///
  /// In sw, this message translates to:
  /// **'Tenganisha'**
  String get disconnectPrinter;

  /// No description provided for @connected.
  ///
  /// In sw, this message translates to:
  /// **'Imeunganishwa'**
  String get connected;

  /// No description provided for @disconnected.
  ///
  /// In sw, this message translates to:
  /// **'Imetenganishwa'**
  String get disconnected;

  /// No description provided for @savedPrinter.
  ///
  /// In sw, this message translates to:
  /// **'Printa Iliyohifadhiwa'**
  String get savedPrinter;

  /// No description provided for @paperSize.
  ///
  /// In sw, this message translates to:
  /// **'Ukubwa wa Karatasi'**
  String get paperSize;

  /// No description provided for @mm58.
  ///
  /// In sw, this message translates to:
  /// **'58mm'**
  String get mm58;

  /// No description provided for @mm80.
  ///
  /// In sw, this message translates to:
  /// **'80mm'**
  String get mm80;

  /// No description provided for @testPrint.
  ///
  /// In sw, this message translates to:
  /// **'Chapisha Jaribio'**
  String get testPrint;

  /// No description provided for @autoPrintAfterSale.
  ///
  /// In sw, this message translates to:
  /// **'Chapisha moja kwa moja baada ya mauzo'**
  String get autoPrintAfterSale;

  /// No description provided for @printerNotConnected.
  ///
  /// In sw, this message translates to:
  /// **'Printa haijaunganishwa'**
  String get printerNotConnected;

  /// No description provided for @connectionFailed.
  ///
  /// In sw, this message translates to:
  /// **'Imeishindwa kuunganisha'**
  String get connectionFailed;

  /// No description provided for @printerType.
  ///
  /// In sw, this message translates to:
  /// **'Aina ya Printa'**
  String get printerType;

  /// No description provided for @changePrinter.
  ///
  /// In sw, this message translates to:
  /// **'Badilisha Printa'**
  String get changePrinter;

  /// No description provided for @efdSettings.
  ///
  /// In sw, this message translates to:
  /// **'Mipangilio ya EFD'**
  String get efdSettings;

  /// No description provided for @traRegistration.
  ///
  /// In sw, this message translates to:
  /// **'Usajili wa TRA'**
  String get traRegistration;

  /// No description provided for @vrnNumber.
  ///
  /// In sw, this message translates to:
  /// **'Namba ya VRN'**
  String get vrnNumber;

  /// No description provided for @efdSerialNumber.
  ///
  /// In sw, this message translates to:
  /// **'Namba ya Serial ya EFD'**
  String get efdSerialNumber;

  /// No description provided for @registerDevice.
  ///
  /// In sw, this message translates to:
  /// **'Sajili Kifaa'**
  String get registerDevice;

  /// No description provided for @testConnection.
  ///
  /// In sw, this message translates to:
  /// **'Jaribu Muunganisho'**
  String get testConnection;

  /// No description provided for @efdEnabled.
  ///
  /// In sw, this message translates to:
  /// **'EFD Imewashwa'**
  String get efdEnabled;

  /// No description provided for @efdDisabled.
  ///
  /// In sw, this message translates to:
  /// **'EFD Imezimwa'**
  String get efdDisabled;

  /// No description provided for @efdEnvironment.
  ///
  /// In sw, this message translates to:
  /// **'Mazingira'**
  String get efdEnvironment;

  /// No description provided for @sandbox.
  ///
  /// In sw, this message translates to:
  /// **'Majaribio'**
  String get sandbox;

  /// No description provided for @production.
  ///
  /// In sw, this message translates to:
  /// **'Uzalishaji'**
  String get production;

  /// No description provided for @fiscalReceipt.
  ///
  /// In sw, this message translates to:
  /// **'Risiti ya Kodi'**
  String get fiscalReceipt;

  /// No description provided for @verificationCode.
  ///
  /// In sw, this message translates to:
  /// **'Nambari ya Uthibitisho'**
  String get verificationCode;

  /// No description provided for @pendingSubmissions.
  ///
  /// In sw, this message translates to:
  /// **'Risiti Zinazosubiri'**
  String get pendingSubmissions;

  /// No description provided for @retryFailed.
  ///
  /// In sw, this message translates to:
  /// **'Jaribu Tena Zilizoshindwa'**
  String get retryFailed;

  /// No description provided for @taxCategory.
  ///
  /// In sw, this message translates to:
  /// **'Aina ya Kodi'**
  String get taxCategory;

  /// No description provided for @standardRate.
  ///
  /// In sw, this message translates to:
  /// **'Kiwango cha Kawaida (18%)'**
  String get standardRate;

  /// No description provided for @zeroRated.
  ///
  /// In sw, this message translates to:
  /// **'Kiwango Sifuri'**
  String get zeroRated;

  /// No description provided for @exempt.
  ///
  /// In sw, this message translates to:
  /// **'Imesamehewa'**
  String get exempt;

  /// No description provided for @efdRegistered.
  ///
  /// In sw, this message translates to:
  /// **'EFD Imesajiliwa'**
  String get efdRegistered;

  /// No description provided for @efdNotRegistered.
  ///
  /// In sw, this message translates to:
  /// **'EFD Haijasajiliwa'**
  String get efdNotRegistered;

  /// No description provided for @efdAllSubmitted.
  ///
  /// In sw, this message translates to:
  /// **'Risiti zote zimetumwa'**
  String get efdAllSubmitted;

  /// No description provided for @whatsappReceipts.
  ///
  /// In sw, this message translates to:
  /// **'Risiti za WhatsApp'**
  String get whatsappReceipts;

  /// No description provided for @whatsappSettings.
  ///
  /// In sw, this message translates to:
  /// **'Utoaji wa risiti kwa WhatsApp'**
  String get whatsappSettings;

  /// No description provided for @deliveryMode.
  ///
  /// In sw, this message translates to:
  /// **'Njia ya Utoaji'**
  String get deliveryMode;

  /// No description provided for @automatic.
  ///
  /// In sw, this message translates to:
  /// **'Otomatiki'**
  String get automatic;

  /// No description provided for @automaticDesc.
  ///
  /// In sw, this message translates to:
  /// **'Tuma risiti moja kwa moja baada ya kila mauzo yenye nambari ya simu'**
  String get automaticDesc;

  /// No description provided for @prompted.
  ///
  /// In sw, this message translates to:
  /// **'Kwa Ruhusa'**
  String get prompted;

  /// No description provided for @promptedDesc.
  ///
  /// In sw, this message translates to:
  /// **'Karani anachagua kutuma baada ya kila mauzo'**
  String get promptedDesc;

  /// No description provided for @smsFallback.
  ///
  /// In sw, this message translates to:
  /// **'SMS Mbadala'**
  String get smsFallback;

  /// No description provided for @smsFallbackDesc.
  ///
  /// In sw, this message translates to:
  /// **'Tuma SMS ikiwa WhatsApp itashindwa'**
  String get smsFallbackDesc;

  /// No description provided for @marketingFooter.
  ///
  /// In sw, this message translates to:
  /// **'Ujumbe wa Masoko'**
  String get marketingFooter;

  /// No description provided for @marketingFooterHint.
  ///
  /// In sw, this message translates to:
  /// **'Ujumbe maalum unaoongezwa kwenye risiti...'**
  String get marketingFooterHint;

  /// No description provided for @sendWhatsAppReceipt.
  ///
  /// In sw, this message translates to:
  /// **'Tuma Risiti kwa WhatsApp'**
  String get sendWhatsAppReceipt;

  /// No description provided for @resendReceipt.
  ///
  /// In sw, this message translates to:
  /// **'Tuma Tena Risiti'**
  String get resendReceipt;

  /// No description provided for @receiptSent.
  ///
  /// In sw, this message translates to:
  /// **'Imetumwa'**
  String get receiptSent;

  /// No description provided for @receiptDelivered.
  ///
  /// In sw, this message translates to:
  /// **'Imepokelewa'**
  String get receiptDelivered;

  /// No description provided for @receiptFailed.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kutuma risiti'**
  String get receiptFailed;

  /// No description provided for @receiptPending.
  ///
  /// In sw, this message translates to:
  /// **'Inatuma...'**
  String get receiptPending;

  /// No description provided for @receiptStatus.
  ///
  /// In sw, this message translates to:
  /// **'Hali ya Risiti'**
  String get receiptStatus;

  /// No description provided for @sendingReceipt.
  ///
  /// In sw, this message translates to:
  /// **'Inatuma risiti...'**
  String get sendingReceipt;

  /// No description provided for @enterCustomerPhone.
  ///
  /// In sw, this message translates to:
  /// **'Weka Nambari ya Simu ya Mteja'**
  String get enterCustomerPhone;

  /// No description provided for @whatsappReceiptSent.
  ///
  /// In sw, this message translates to:
  /// **'Risiti ya WhatsApp imetumwa!'**
  String get whatsappReceiptSent;

  /// No description provided for @testWhatsApp.
  ///
  /// In sw, this message translates to:
  /// **'Tuma Ujumbe wa Majaribio'**
  String get testWhatsApp;

  /// No description provided for @whatsappEnabled.
  ///
  /// In sw, this message translates to:
  /// **'Risiti za WhatsApp zimewashwa'**
  String get whatsappEnabled;

  /// No description provided for @whatsappDisabled.
  ///
  /// In sw, this message translates to:
  /// **'Risiti za WhatsApp zimezimwa'**
  String get whatsappDisabled;

  /// No description provided for @noPhoneNumber.
  ///
  /// In sw, this message translates to:
  /// **'Hakuna nambari ya simu'**
  String get noPhoneNumber;

  /// No description provided for @sentViaWhatsapp.
  ///
  /// In sw, this message translates to:
  /// **'Imetumwa kwa WhatsApp'**
  String get sentViaWhatsapp;

  /// No description provided for @sentViaSms.
  ///
  /// In sw, this message translates to:
  /// **'Imetumwa kwa SMS'**
  String get sentViaSms;

  /// No description provided for @changesSaved.
  ///
  /// In sw, this message translates to:
  /// **'Mabadiliko yamehifadhiwa'**
  String get changesSaved;

  /// No description provided for @unsavedChanges.
  ///
  /// In sw, this message translates to:
  /// **'Mabadiliko Hayajahifadhiwa'**
  String get unsavedChanges;

  /// No description provided for @unsavedChangesMessage.
  ///
  /// In sw, this message translates to:
  /// **'Una mabadiliko ambayo hayajahifadhiwa. Unataka kuyatumia kabla ya kuondoka?'**
  String get unsavedChangesMessage;

  /// No description provided for @discard.
  ///
  /// In sw, this message translates to:
  /// **'Tupa'**
  String get discard;

  /// No description provided for @apply.
  ///
  /// In sw, this message translates to:
  /// **'Tumia'**
  String get apply;

  /// No description provided for @applyChanges.
  ///
  /// In sw, this message translates to:
  /// **'Tumia Mabadiliko'**
  String get applyChanges;

  /// No description provided for @chooseLayoutPreset.
  ///
  /// In sw, this message translates to:
  /// **'Chagua mpangilio wa haraka wa dashibodi yako'**
  String get chooseLayoutPreset;

  /// No description provided for @classicDesc.
  ///
  /// In sw, this message translates to:
  /// **'Muhtasari kamili'**
  String get classicDesc;

  /// No description provided for @analyticsDesc.
  ///
  /// In sw, this message translates to:
  /// **'Inazingatia data'**
  String get analyticsDesc;

  /// No description provided for @compactDesc.
  ///
  /// In sw, this message translates to:
  /// **'Mtazamo mfupi'**
  String get compactDesc;

  /// No description provided for @widgetsVisible.
  ///
  /// In sw, this message translates to:
  /// **'vinaonekana'**
  String get widgetsVisible;

  /// No description provided for @todayStatsDesc.
  ///
  /// In sw, this message translates to:
  /// **'Mauzo, oda na mapato ya leo'**
  String get todayStatsDesc;

  /// No description provided for @quickActionsDesc.
  ///
  /// In sw, this message translates to:
  /// **'Ufikiaji wa haraka wa kazi za kawaida'**
  String get quickActionsDesc;

  /// No description provided for @lowStockAlertDesc.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa zinazopungua'**
  String get lowStockAlertDesc;

  /// No description provided for @recentTransactionsDesc.
  ///
  /// In sw, this message translates to:
  /// **'Shughuli za mauzo za hivi karibuni'**
  String get recentTransactionsDesc;

  /// No description provided for @weeklySummaryDesc.
  ///
  /// In sw, this message translates to:
  /// **'Chati ya utendaji wa siku 7'**
  String get weeklySummaryDesc;

  /// No description provided for @topProductsDesc.
  ///
  /// In sw, this message translates to:
  /// **'Bidhaa zinazouzwa zaidi'**
  String get topProductsDesc;

  /// No description provided for @profitBreakdownDesc.
  ///
  /// In sw, this message translates to:
  /// **'Mapato, gharama na uchambuzi wa faida'**
  String get profitBreakdownDesc;

  /// No description provided for @landingPageDesc.
  ///
  /// In sw, this message translates to:
  /// **'Skrini inayoonyeshwa unapofungua programu'**
  String get landingPageDesc;

  /// No description provided for @totalCosts.
  ///
  /// In sw, this message translates to:
  /// **'Gharama Zote'**
  String get totalCosts;

  /// No description provided for @mobileAccess.
  ///
  /// In sw, this message translates to:
  /// **'Ufikiaji wa Simu'**
  String get mobileAccess;

  /// No description provided for @accessApproved.
  ///
  /// In sw, this message translates to:
  /// **'Ufikiaji Umeidhinishwa'**
  String get accessApproved;

  /// No description provided for @pendingApproval.
  ///
  /// In sw, this message translates to:
  /// **'Inasubiri Idhini'**
  String get pendingApproval;

  /// No description provided for @requestRejected.
  ///
  /// In sw, this message translates to:
  /// **'Ombi Limekataliwa'**
  String get requestRejected;

  /// No description provided for @accessRevoked.
  ///
  /// In sw, this message translates to:
  /// **'Ufikiaji Umefutwa'**
  String get accessRevoked;

  /// No description provided for @registerThisDevice.
  ///
  /// In sw, this message translates to:
  /// **'Sajili Kifaa Hiki'**
  String get registerThisDevice;

  /// No description provided for @registerThisDeviceDesc.
  ///
  /// In sw, this message translates to:
  /// **'Ili kutumia POS kwenye kifaa hiki, unahitaji kusajili kwanza.'**
  String get registerThisDeviceDesc;

  /// No description provided for @requestPending.
  ///
  /// In sw, this message translates to:
  /// **'Ombi Linasubiri'**
  String get requestPending;

  /// No description provided for @requestPendingDesc.
  ///
  /// In sw, this message translates to:
  /// **'Ombi lako linakaguliwa na msimamizi. Tafadhali angalia baadaye.'**
  String get requestPendingDesc;

  /// No description provided for @requestRejectedDesc.
  ///
  /// In sw, this message translates to:
  /// **'Ombi lako limekataliwa. Tafadhali wasiliana na msaada.'**
  String get requestRejectedDesc;

  /// No description provided for @accessRevokedDesc.
  ///
  /// In sw, this message translates to:
  /// **'Ufikiaji wako wa simu umefutwa. Tafadhali wasiliana na msaada.'**
  String get accessRevokedDesc;

  /// No description provided for @requestMobileAccess.
  ///
  /// In sw, this message translates to:
  /// **'Omba Ufikiaji wa Simu'**
  String get requestMobileAccess;

  /// No description provided for @requestMobileAccessDesc.
  ///
  /// In sw, this message translates to:
  /// **'Tuma ombi la kutumia programu ya POS ya simu kwa biashara yako.'**
  String get requestMobileAccessDesc;

  /// No description provided for @mobileAccessRequired.
  ///
  /// In sw, this message translates to:
  /// **'Ufikiaji wa Simu Unahitajika'**
  String get mobileAccessRequired;

  /// No description provided for @mobileAccessRequiredDesc.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali mwambie mmiliki wa kampuni yako aombe ufikiaji wa simu.'**
  String get mobileAccessRequiredDesc;

  /// No description provided for @submitNewRequest.
  ///
  /// In sw, this message translates to:
  /// **'Unaweza kutuma ombi jipya:'**
  String get submitNewRequest;

  /// No description provided for @registerDeviceBtn.
  ///
  /// In sw, this message translates to:
  /// **'Sajili Kifaa'**
  String get registerDeviceBtn;

  /// No description provided for @checkStatus.
  ///
  /// In sw, this message translates to:
  /// **'Angalia Hali'**
  String get checkStatus;

  /// No description provided for @signOut.
  ///
  /// In sw, this message translates to:
  /// **'Ondoka'**
  String get signOut;

  /// No description provided for @submitRequest.
  ///
  /// In sw, this message translates to:
  /// **'Tuma Ombi'**
  String get submitRequest;

  /// No description provided for @reasonForRequest.
  ///
  /// In sw, this message translates to:
  /// **'Sababu ya Ombi'**
  String get reasonForRequest;

  /// No description provided for @reasonForRequestHint.
  ///
  /// In sw, this message translates to:
  /// **'Eleza kwa nini unahitaji ufikiaji wa simu...'**
  String get reasonForRequestHint;

  /// No description provided for @expectedDevices.
  ///
  /// In sw, this message translates to:
  /// **'Idadi ya Vifaa Vinavyotarajiwa'**
  String get expectedDevices;

  /// No description provided for @expectedDevicesHint.
  ///
  /// In sw, this message translates to:
  /// **'Vifaa vingapi vitatumia programu?'**
  String get expectedDevicesHint;

  /// No description provided for @provideReason.
  ///
  /// In sw, this message translates to:
  /// **'Tafadhali toa sababu ya kuomba ufikiaji wa simu'**
  String get provideReason;

  /// No description provided for @requestSubmitted.
  ///
  /// In sw, this message translates to:
  /// **'Ombi la ufikiaji wa simu limetumwa kwa mafanikio'**
  String get requestSubmitted;

  /// No description provided for @failedToSubmitRequest.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kutuma ombi. Tafadhali jaribu tena.'**
  String get failedToSubmitRequest;

  /// No description provided for @failedToRegisterDevice.
  ///
  /// In sw, this message translates to:
  /// **'Imeshindwa kusajili kifaa. Tafadhali jaribu tena.'**
  String get failedToRegisterDevice;

  /// No description provided for @approvedSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Kampuni yako ina ufikiaji wa simu. Sajili kifaa hiki kuendelea.'**
  String get approvedSubtitle;

  /// No description provided for @pendingSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Ombi lako linakaguliwa.'**
  String get pendingSubtitle;

  /// No description provided for @rejectedSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Ombi lako halikuidhinishwa.'**
  String get rejectedSubtitle;

  /// No description provided for @revokedSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Wasiliana na msaada kwa usaidizi.'**
  String get revokedSubtitle;

  /// No description provided for @ownerSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Omba ufikiaji wa kutumia programu ya POS ya simu.'**
  String get ownerSubtitle;

  /// No description provided for @nonOwnerSubtitle.
  ///
  /// In sw, this message translates to:
  /// **'Wasiliana na mmiliki wa kampuni yako kwa ufikiaji.'**
  String get nonOwnerSubtitle;

  /// No description provided for @comingSoon.
  ///
  /// In sw, this message translates to:
  /// **'Inakuja Hivi Karibuni'**
  String get comingSoon;

  /// No description provided for @whatsappComingSoon.
  ///
  /// In sw, this message translates to:
  /// **'Risiti za WhatsApp zinakuja hivi karibuni'**
  String get whatsappComingSoon;
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
