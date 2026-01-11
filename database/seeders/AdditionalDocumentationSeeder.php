<?php

namespace Database\Seeders;

use App\Models\DocumentationArticle;
use App\Models\DocumentationCategory;
use Illuminate\Database\Seeder;

class AdditionalDocumentationSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'user-management',
                'icon' => 'bi-people',
                'sort_order' => 6,
                'translations' => [
                    'en' => [
                        'name' => 'User Management',
                        'description' => 'Manage staff accounts, permissions, and access control.',
                    ],
                    'sw' => [
                        'name' => 'Usimamizi wa Watumiaji',
                        'description' => 'Simamia akaunti za wafanyakazi, ruhusa, na udhibiti wa ufikiaji.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'creating-staff-accounts',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Creating Staff Accounts',
                                'excerpt' => 'Learn how to add new staff members to your business.',
                                'content' => "# Creating Staff Accounts\n\nAs a company owner, you can add staff members (cashiers) to help run your business.\n\n## How to Add Staff\n\n1. Go to **Staff** in the menu\n2. Click **Add Staff**\n3. Fill in the details:\n   - **Name**: Full name of the staff member\n   - **Email**: Their email address\n   - **Login Method**: Choose how they'll access the system\n4. Select which branches they can access\n5. Set their permissions\n6. Click **Create Staff Member**\n\n## Login Methods\n\nYou can choose how your staff will log in:\n\n### Email Only\nStaff receives an invitation email with a link to set their password. Best for managers who need full access.\n\n### PIN Only\nYou set a 4-digit PIN that you share with them verbally. Best for cashiers who need quick access at the point of sale.\n\n### Both (Recommended)\nStaff gets both email invitation AND a PIN. They can use either method to log in.\n\n## After Creation\n\n- **Email Method**: Staff receives an invitation email. They must click the link and set their password within 7 days.\n- **PIN Method**: Share the PIN securely with your staff. They can immediately log in using email + PIN.\n\n## Tips\n\n- Use PIN login for cashiers who share a device\n- Use email login for staff with dedicated computers\n- Review permissions carefully before creating the account",
                            ],
                            'sw' => [
                                'title' => 'Kuunda Akaunti za Wafanyakazi',
                                'excerpt' => 'Jifunze jinsi ya kuongeza wafanyakazi wapya kwenye biashara yako.',
                                'content' => "# Kuunda Akaunti za Wafanyakazi\n\nKama mmiliki wa kampuni, unaweza kuongeza wafanyakazi (wakusanyaji) kusaidia kuendesha biashara yako.\n\n## Jinsi ya Kuongeza Wafanyakazi\n\n1. Nenda **Wafanyakazi** kwenye menyu\n2. Bonyeza **Ongeza Mfanyakazi**\n3. Jaza maelezo:\n   - **Jina**: Jina kamili la mfanyakazi\n   - **Barua pepe**: Anwani yao ya barua pepe\n   - **Njia ya Kuingia**: Chagua jinsi watakavyofikia mfumo\n4. Chagua matawi wanayoweza kufikia\n5. Weka ruhusa zao\n6. Bonyeza **Unda Mfanyakazi**\n\n## Njia za Kuingia\n\nUnaweza kuchagua jinsi wafanyakazi wako watakavyoingia:\n\n### Barua pepe Tu\nMfanyakazi anapokea barua pepe ya mwaliko na kiungo cha kuweka nenosiri lao. Bora kwa wasimamizi wanaohitaji ufikiaji kamili.\n\n### PIN Tu\nUnaweka PIN ya tarakimu 4 ambayo unashiriki nao kwa mdomo. Bora kwa wakusanyaji wanaohitaji ufikiaji wa haraka kwenye point of sale.\n\n### Zote Mbili (Inapendekezwa)\nMfanyakazi anapata mwaliko wa barua pepe NA PIN. Wanaweza kutumia njia yoyote kuingia.\n\n## Baada ya Kuunda\n\n- **Njia ya Barua pepe**: Mfanyakazi anapokea barua pepe ya mwaliko. Lazima wabonyeze kiungo na kuweka nenosiri lao ndani ya siku 7.\n- **Njia ya PIN**: Shiriki PIN kwa usalama na wafanyakazi wako. Wanaweza kuingia mara moja kwa kutumia barua pepe + PIN.\n\n## Vidokezo\n\n- Tumia kuingia kwa PIN kwa wakusanyaji wanaoshiriki kifaa\n- Tumia kuingia kwa barua pepe kwa wafanyakazi wenye kompyuta zao\n- Kagua ruhusa kwa makini kabla ya kuunda akaunti",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'pin-login',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'PIN Login System',
                                'excerpt' => 'Quick access for cashiers using a 4-digit PIN.',
                                'content' => "# PIN Login System\n\nThe PIN login system allows cashiers to quickly log in without typing a password.\n\n## How PIN Login Works\n\n1. Go to the PIN Login page (`/pin-login`)\n2. Enter your email address\n3. Enter your 4-digit PIN\n4. Click **Sign In with PIN**\n\n## Quick Switch Mode\n\nFor shared POS terminals, multiple cashiers can switch shifts without full logout:\n\n1. The terminal stays logged in to the company\n2. Cashiers enter their PIN to start their shift\n3. At shift end, the next cashier enters their PIN\n4. All transactions are tracked to the correct cashier\n\n## Setting Up a PIN\n\n### For New Staff\nWhen creating a staff account, select \"PIN\" or \"Both\" as the login method, then set the PIN.\n\n### For Existing Staff\n1. Go to **Staff**\n2. Click on the staff member\n3. Click **Reset PIN**\n4. Share the new PIN with them securely\n\n## Security Tips\n\n- Don't share PINs between staff members\n- Change PINs periodically\n- Use unique PINs for each employee\n- Don't write PINs on visible notes\n\n## Troubleshooting\n\n**PIN not working?**\n- Make sure you're entering exactly 4 digits\n- Check that your account is active\n- Ask your admin to reset your PIN",
                            ],
                            'sw' => [
                                'title' => 'Mfumo wa Kuingia kwa PIN',
                                'excerpt' => 'Ufikiaji wa haraka kwa wakusanyaji kwa kutumia PIN ya tarakimu 4.',
                                'content' => "# Mfumo wa Kuingia kwa PIN\n\nMfumo wa kuingia kwa PIN unaruhusu wakusanyaji kuingia haraka bila kuandika nenosiri.\n\n## Jinsi Kuingia kwa PIN Inavyofanya Kazi\n\n1. Nenda ukurasa wa Kuingia kwa PIN (`/pin-login`)\n2. Ingiza anwani yako ya barua pepe\n3. Ingiza PIN yako ya tarakimu 4\n4. Bonyeza **Ingia kwa PIN**\n\n## Hali ya Kubadilisha Haraka\n\nKwa vituo vya POS vinavyoshirikiwa, wakusanyaji wengi wanaweza kubadilisha zamu bila kuondoka kabisa:\n\n1. Kituo kinabaki kuingia kwenye kampuni\n2. Wakusanyaji wanaingiza PIN yao kuanza zamu yao\n3. Mwishoni mwa zamu, mkusanyaji anayefuata anaingiza PIN yao\n4. Miamala yote inafuatiliwa kwa mkusanyaji sahihi\n\n## Kuweka PIN\n\n### Kwa Wafanyakazi Wapya\nWakati wa kuunda akaunti ya mfanyakazi, chagua \"PIN\" au \"Zote Mbili\" kama njia ya kuingia, kisha weka PIN.\n\n### Kwa Wafanyakazi Waliopo\n1. Nenda **Wafanyakazi**\n2. Bonyeza mfanyakazi\n3. Bonyeza **Weka upya PIN**\n4. Shiriki PIN mpya nao kwa usalama\n\n## Vidokezo vya Usalama\n\n- Usishiriki PIN kati ya wafanyakazi\n- Badilisha PIN mara kwa mara\n- Tumia PIN za kipekee kwa kila mfanyakazi\n- Usiandike PIN kwenye maelezo yanayoonekana\n\n## Utatuzi wa Matatizo\n\n**PIN haifanyi kazi?**\n- Hakikisha unaingiza tarakimu 4 haswa\n- Angalia akaunti yako iko hai\n- Mwambie msimamizi wako aweke upya PIN yako",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'managing-permissions',
                        'sort_order' => 3,
                        'translations' => [
                            'en' => [
                                'title' => 'Managing Permissions',
                                'excerpt' => 'Control what each staff member can access and do.',
                                'content' => "# Managing Permissions\n\nPermissions control what each staff member can see and do in the system.\n\n## Available Permissions\n\n### Transactions\n- **Void Transactions**: Can void completed sales\n- **Apply Discounts**: Can apply custom discounts\n\n### Reports\n- **View Reports**: Access to sales and inventory reports\n- **Export Reports**: Can export reports to PDF/Excel\n\n### Inventory\n- **Manage Inventory**: Can adjust stock levels\n- **View Cost Prices**: Can see product cost prices\n\n### Products\n- **Manage Products**: Can create, edit, and delete products\n- **Manage Categories**: Can create, edit, and delete categories\n\n### Administration\n- **Manage Users**: Can create and edit staff accounts\n- **Manage Branches**: Can configure branches\n- **Manage Settings**: Can modify company settings\n\n## How to Set Permissions\n\n1. Go to **Staff**\n2. Click on a staff member\n3. Click **Permissions**\n4. Check the permissions you want to grant\n5. Click **Save Permissions**\n\n## Permission Tips\n\n- **Cashiers**: Usually only need basic POS access (no special permissions)\n- **Shift Supervisors**: Add void transactions, apply discounts, view reports\n- **Managers**: Add manage inventory, manage products\n- **Company Owners**: Automatically have all permissions\n\n## Important Notes\n\n- Company owners always have all permissions\n- Changes take effect immediately\n- Users must refresh the page to see new permissions",
                            ],
                            'sw' => [
                                'title' => 'Kusimamia Ruhusa',
                                'excerpt' => 'Dhibiti kile ambacho kila mfanyakazi anaweza kufikia na kufanya.',
                                'content' => "# Kusimamia Ruhusa\n\nRuhusa zinadhibiti kile ambacho kila mfanyakazi anaweza kuona na kufanya kwenye mfumo.\n\n## Ruhusa Zinazopatikana\n\n### Miamala\n- **Batilisha Miamala**: Anaweza kubatilisha mauzo yaliyokamilika\n- **Tumia Punguzo**: Anaweza kutumia punguzo maalum\n\n### Ripoti\n- **Tazama Ripoti**: Ufikiaji wa ripoti za mauzo na stoku\n- **Hamisha Ripoti**: Anaweza kuhamisha ripoti kwa PDF/Excel\n\n### Stoku\n- **Simamia Stoku**: Anaweza kurekebisha viwango vya stoku\n- **Tazama Bei za Gharama**: Anaweza kuona bei za gharama za bidhaa\n\n### Bidhaa\n- **Simamia Bidhaa**: Anaweza kuunda, kuhariri, na kufuta bidhaa\n- **Simamia Makundi**: Anaweza kuunda, kuhariri, na kufuta makundi\n\n### Utawala\n- **Simamia Watumiaji**: Anaweza kuunda na kuhariri akaunti za wafanyakazi\n- **Simamia Matawi**: Anaweza kusanidi matawi\n- **Simamia Mipangilio**: Anaweza kubadilisha mipangilio ya kampuni\n\n## Jinsi ya Kuweka Ruhusa\n\n1. Nenda **Wafanyakazi**\n2. Bonyeza mfanyakazi\n3. Bonyeza **Ruhusa**\n4. Angalia ruhusa unazotaka kutoa\n5. Bonyeza **Hifadhi Ruhusa**\n\n## Vidokezo vya Ruhusa\n\n- **Wakusanyaji**: Kawaida wanahitaji ufikiaji wa msingi wa POS tu (hakuna ruhusa maalum)\n- **Wasimamizi wa Zamu**: Ongeza kubatilisha miamala, kutumia punguzo, kutazama ripoti\n- **Wasimamizi**: Ongeza kusimamia stoku, kusimamia bidhaa\n- **Wamiliki wa Kampuni**: Kiotomatiki wana ruhusa zote\n\n## Maelezo Muhimu\n\n- Wamiliki wa kampuni daima wana ruhusa zote\n- Mabadiliko yanaanza kufanya kazi mara moja\n- Watumiaji lazima waonyeshe upya ukurasa kuona ruhusa mpya",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'resetting-passwords',
                        'sort_order' => 4,
                        'translations' => [
                            'en' => [
                                'title' => 'Resetting Passwords & PINs',
                                'excerpt' => 'How to reset staff login credentials.',
                                'content' => "# Resetting Passwords & PINs\n\nSometimes staff need their credentials reset.\n\n## Resetting a Password\n\n### Staff Can Reset Their Own\n1. Go to the login page\n2. Click **Forgot Password**\n3. Enter email address\n4. Check email for reset link\n5. Click link and set new password\n\n### Admin Can Resend Invitation\n1. Go to **Staff**\n2. Find the staff member\n3. Click **Resend Invitation**\n4. Staff receives new email to set password\n\n## Resetting a PIN\n\n1. Go to **Staff**\n2. Click on the staff member\n3. Click **Reset PIN**\n4. A new 4-digit PIN is generated\n5. Share the new PIN with the staff member\n\n## Deactivating Accounts\n\nIf a staff member leaves:\n\n1. Go to **Staff**\n2. Find the staff member\n3. Click the **Deactivate** button\n4. Their account is immediately disabled\n\n**Note**: Deactivated accounts can be reactivated later. This preserves their transaction history.\n\n## Security Best Practices\n\n- Reset credentials if you suspect they've been compromised\n- Deactivate accounts immediately when staff leave\n- Review active accounts regularly\n- Don't share login credentials between staff",
                            ],
                            'sw' => [
                                'title' => 'Kuweka Upya Nenosiri na PIN',
                                'excerpt' => 'Jinsi ya kuweka upya vyeti vya kuingia vya wafanyakazi.',
                                'content' => "# Kuweka Upya Nenosiri na PIN\n\nWakati mwingine wafanyakazi wanahitaji vyeti vyao viwekwe upya.\n\n## Kuweka Upya Nenosiri\n\n### Wafanyakazi Wanaweza Kuweka Upya Wao Wenyewe\n1. Nenda ukurasa wa kuingia\n2. Bonyeza **Umesahau Nenosiri**\n3. Ingiza anwani ya barua pepe\n4. Angalia barua pepe kwa kiungo cha kuweka upya\n5. Bonyeza kiungo na uweke nenosiri jipya\n\n### Msimamizi Anaweza Kutuma Tena Mwaliko\n1. Nenda **Wafanyakazi**\n2. Tafuta mfanyakazi\n3. Bonyeza **Tuma Tena Mwaliko**\n4. Mfanyakazi anapokea barua pepe mpya ya kuweka nenosiri\n\n## Kuweka Upya PIN\n\n1. Nenda **Wafanyakazi**\n2. Bonyeza mfanyakazi\n3. Bonyeza **Weka Upya PIN**\n4. PIN mpya ya tarakimu 4 inaundwa\n5. Shiriki PIN mpya na mfanyakazi\n\n## Kuzima Akaunti\n\nIkiwa mfanyakazi anaondoka:\n\n1. Nenda **Wafanyakazi**\n2. Tafuta mfanyakazi\n3. Bonyeza kitufe cha **Zima**\n4. Akaunti yao imezimwa mara moja\n\n**Kumbuka**: Akaunti zilizozimwa zinaweza kuwashwa tena baadaye. Hii inahifadhi historia yao ya miamala.\n\n## Mazoea Bora ya Usalama\n\n- Weka upya vyeti ukishuku vimeathiriwa\n- Zima akaunti mara moja wafanyakazi wanapoondoka\n- Kagua akaunti zinazofanya kazi mara kwa mara\n- Usishiriki vyeti vya kuingia kati ya wafanyakazi",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'branch-operations',
                'icon' => 'bi-building',
                'sort_order' => 7,
                'translations' => [
                    'en' => [
                        'name' => 'Branch Operations',
                        'description' => 'Manage multiple business locations effectively.',
                    ],
                    'sw' => [
                        'name' => 'Shughuli za Matawi',
                        'description' => 'Simamia maeneo mengi ya biashara kwa ufanisi.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'setting-up-branches',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Setting Up Multiple Branches',
                                'excerpt' => 'Learn how to configure your business for multiple locations.',
                                'content' => "# Setting Up Multiple Branches\n\nSasampa POS supports businesses with multiple locations.\n\n## Creating a Branch\n\n1. Go to **Branches** in the menu\n2. Click **Add Branch**\n3. Fill in the details:\n   - **Name**: Branch name (e.g., \"Downtown Store\")\n   - **Code**: Short identifier (e.g., \"DT01\")\n   - **Address**: Physical location\n   - **Phone**: Contact number\n   - **Email**: Branch email\n4. Choose if this is the main branch\n5. Click **Create Branch**\n\n## Branch Modes\n\n### Shared Products (Default)\nAll branches share the same product catalog. Good for:\n- Chain stores\n- Franchises\n- Stores with identical inventory\n\n### Independent Products\nEach branch manages its own products. Good for:\n- Different product lines per location\n- Regional pricing differences\n- Unique inventory per store\n\n## Main Branch\n\nOne branch is designated as \"main\" (headquarters). This is typically where:\n- Company settings are managed\n- Reports aggregate data from all branches\n- New products are first added\n\n## Branch Codes\n\nUse short codes for:\n- Quick identification on receipts\n- Report filtering\n- Staff assignment\n\n## Tips\n\n- Set up branches before adding staff\n- Use consistent naming conventions\n- Keep contact info updated",
                            ],
                            'sw' => [
                                'title' => 'Kuweka Matawi Mengi',
                                'excerpt' => 'Jifunze jinsi ya kusanidi biashara yako kwa maeneo mengi.',
                                'content' => "# Kuweka Matawi Mengi\n\nSasampa POS inasaidia biashara zenye maeneo mengi.\n\n## Kuunda Tawi\n\n1. Nenda **Matawi** kwenye menyu\n2. Bonyeza **Ongeza Tawi**\n3. Jaza maelezo:\n   - **Jina**: Jina la tawi (mfano, \"Duka la Mjini\")\n   - **Msimbo**: Kitambulisho kifupi (mfano, \"MJ01\")\n   - **Anwani**: Mahali halisi\n   - **Simu**: Nambari ya mawasiliano\n   - **Barua pepe**: Barua pepe ya tawi\n4. Chagua kama hili ni tawi kuu\n5. Bonyeza **Unda Tawi**\n\n## Hali za Tawi\n\n### Bidhaa Zinazoshirikiwa (Chaguo-msingi)\nMatawi yote yanashiriki orodha sawa ya bidhaa. Nzuri kwa:\n- Maduka ya mnyororo\n- Haki za biashara\n- Maduka yenye stoku inayofanana\n\n### Bidhaa Huru\nKila tawi linasimamia bidhaa zake. Nzuri kwa:\n- Mistari tofauti ya bidhaa kwa kila eneo\n- Tofauti za bei za kikanda\n- Stoku ya kipekee kwa kila duka\n\n## Tawi Kuu\n\nTawi moja linachaguliwa kama \"kuu\" (makao makuu). Hapa kawaida:\n- Mipangilio ya kampuni inasimamiwa\n- Ripoti zinakusanya data kutoka matawi yote\n- Bidhaa mpya zinaongezwa kwanza\n\n## Misimbo ya Tawi\n\nTumia misimbo mifupi kwa:\n- Utambuzi wa haraka kwenye risiti\n- Uchujaji wa ripoti\n- Ugawaji wa wafanyakazi\n\n## Vidokezo\n\n- Weka matawi kabla ya kuongeza wafanyakazi\n- Tumia mkataba wa kutaja unaofanana\n- Weka habari za mawasiliano zilizoboreshwa",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'assigning-staff-to-branches',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'Assigning Staff to Branches',
                                'excerpt' => 'Control which branches your staff can access.',
                                'content' => "# Assigning Staff to Branches\n\nControl which locations each staff member can work at.\n\n## When Creating Staff\n\n1. On the staff creation form\n2. Check the branches they can access\n3. Select their default branch\n4. Save the staff member\n\n## Modifying Branch Access\n\n1. Go to **Staff**\n2. Click **Edit** on the staff member\n3. Update their branch assignments\n4. Change their default branch if needed\n5. Save changes\n\n## Default Branch\n\nEach staff member has one default branch:\n- This is where they start when logging in\n- Their transactions are recorded to this branch\n- They can switch to other assigned branches\n\n## Switching Branches\n\nStaff can switch between their assigned branches:\n\n1. Click the branch name in the header\n2. Select a different branch from the dropdown\n3. The system switches to that branch's data\n\n## Branch-Specific Data\n\nWhen working at a branch:\n- Products shown depend on branch mode\n- Transactions are recorded to that branch\n- Reports can be filtered by branch\n\n## Tips\n\n- Assign staff to branches they physically work at\n- Set the default branch to their primary location\n- Company owners can access all branches",
                            ],
                            'sw' => [
                                'title' => 'Kugawa Wafanyakazi kwa Matawi',
                                'excerpt' => 'Dhibiti matawi gani wafanyakazi wako wanaweza kufikia.',
                                'content' => "# Kugawa Wafanyakazi kwa Matawi\n\nDhibiti maeneo gani kila mfanyakazi anaweza kufanya kazi.\n\n## Wakati wa Kuunda Wafanyakazi\n\n1. Kwenye fomu ya kuunda mfanyakazi\n2. Angalia matawi wanayoweza kufikia\n3. Chagua tawi lao la chaguo-msingi\n4. Hifadhi mfanyakazi\n\n## Kubadilisha Ufikiaji wa Tawi\n\n1. Nenda **Wafanyakazi**\n2. Bonyeza **Hariri** kwa mfanyakazi\n3. Sasisha ugawaji wao wa matawi\n4. Badilisha tawi lao la chaguo-msingi ikiwa inahitajika\n5. Hifadhi mabadiliko\n\n## Tawi la Chaguo-msingi\n\nKila mfanyakazi ana tawi moja la chaguo-msingi:\n- Hapa ndipo wanapoanza wakati wa kuingia\n- Miamala yao inarekodwa kwenye tawi hili\n- Wanaweza kubadilisha kwenye matawi mengine yaliyogawiwa\n\n## Kubadilisha Matawi\n\nWafanyakazi wanaweza kubadilisha kati ya matawi yao yaliyogawiwa:\n\n1. Bonyeza jina la tawi kwenye kichwa\n2. Chagua tawi tofauti kutoka orodha\n3. Mfumo unabadilika kwenye data ya tawi hilo\n\n## Data ya Tawi Maalum\n\nWakati wa kufanya kazi kwenye tawi:\n- Bidhaa zinazoonyeshwa zinategemea hali ya tawi\n- Miamala inarekodwa kwenye tawi hilo\n- Ripoti zinaweza kuchujwa kwa tawi\n\n## Vidokezo\n\n- Gawa wafanyakazi kwenye matawi wanayofanya kazi kimwili\n- Weka tawi la chaguo-msingi kuwa eneo lao kuu\n- Wamiliki wa kampuni wanaweza kufikia matawi yote",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'branch-reporting',
                        'sort_order' => 3,
                        'translations' => [
                            'en' => [
                                'title' => 'Branch Reporting',
                                'excerpt' => 'Analyze performance across your branches.',
                                'content' => "# Branch Reporting\n\nTrack and compare performance across all your locations.\n\n## Accessing Branch Reports\n\n1. Go to **Reports**\n2. Use the branch filter to select:\n   - **All Branches**: Combined data\n   - **Specific Branch**: Single location data\n\n## Available Metrics\n\n### Per Branch\n- Total sales\n- Transaction count\n- Average transaction value\n- Top selling products\n- Inventory levels\n\n### Comparison\n- Branch vs branch performance\n- Sales trends by location\n- Staff performance per branch\n\n## Dashboard View\n\nThe dashboard shows:\n- Current branch statistics\n- Today's performance\n- Quick comparisons\n\nSwitch branches using the header dropdown to see different locations.\n\n## Best Practices\n\n- Review branch reports weekly\n- Compare similar time periods\n- Identify top and underperforming locations\n- Share insights with branch managers\n\n## Export Options\n\nExport branch data for:\n- Accounting reconciliation\n- Performance reviews\n- Business planning",
                            ],
                            'sw' => [
                                'title' => 'Ripoti za Tawi',
                                'excerpt' => 'Changanua utendaji katika matawi yako.',
                                'content' => "# Ripoti za Tawi\n\nFuatilia na ulinganishe utendaji katika maeneo yako yote.\n\n## Kufikia Ripoti za Tawi\n\n1. Nenda **Ripoti**\n2. Tumia kichujio cha tawi kuchagua:\n   - **Matawi Yote**: Data iliyounganishwa\n   - **Tawi Maalum**: Data ya eneo moja\n\n## Metriki Zinazopatikana\n\n### Kwa Tawi\n- Mauzo ya jumla\n- Idadi ya miamala\n- Thamani ya wastani ya muamala\n- Bidhaa zinazouzwa zaidi\n- Viwango vya stoku\n\n### Ulinganisho\n- Utendaji wa tawi dhidi ya tawi\n- Mwenendo wa mauzo kwa eneo\n- Utendaji wa wafanyakazi kwa tawi\n\n## Mtazamo wa Dashibodi\n\nDashibodi inaonyesha:\n- Takwimu za tawi la sasa\n- Utendaji wa leo\n- Ulinganisho wa haraka\n\nBadilisha matawi kwa kutumia orodha ya kichwa kuona maeneo tofauti.\n\n## Mazoea Bora\n\n- Kagua ripoti za tawi kila wiki\n- Linganisha vipindi sawa vya wakati\n- Tambua maeneo yanayofanya vizuri na yasiyofanya vizuri\n- Shiriki maarifa na wasimamizi wa tawi\n\n## Chaguzi za Kuhamisha\n\nHamisha data ya tawi kwa:\n- Upatanisho wa uhasibu\n- Mapitio ya utendaji\n- Mipango ya biashara",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'advanced-features',
                'icon' => 'bi-gear',
                'sort_order' => 8,
                'translations' => [
                    'en' => [
                        'name' => 'Advanced Features',
                        'description' => 'Learn about advanced system capabilities.',
                    ],
                    'sw' => [
                        'name' => 'Vipengele vya Juu',
                        'description' => 'Jifunze kuhusu uwezo wa juu wa mfumo.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'keyboard-shortcuts',
                        'sort_order' => 1,
                        'translations' => [
                            'en' => [
                                'title' => 'Keyboard Shortcuts',
                                'excerpt' => 'Speed up your workflow with keyboard shortcuts.',
                                'content' => "# Keyboard Shortcuts\n\nWork faster with these keyboard shortcuts.\n\n## Point of Sale\n\n| Shortcut | Action |\n|----------|--------|\n| `/` or `F2` | Focus search bar |\n| `Enter` | Complete checkout |\n| `Esc` | Clear search / Close modal |\n| `+` | Increase item quantity |\n| `-` | Decrease item quantity |\n\n## Navigation\n\n| Shortcut | Action |\n|----------|--------|\n| `Alt + D` | Go to Dashboard |\n| `Alt + P` | Go to POS |\n| `Alt + I` | Go to Inventory |\n| `Alt + R` | Go to Reports |\n\n## General\n\n| Shortcut | Action |\n|----------|--------|\n| `Ctrl + S` | Save (in forms) |\n| `Esc` | Cancel / Close |\n\n## Tips\n\n- Use the search shortcut for quick product lookup\n- Practice shortcuts to speed up checkout\n- Most shortcuts work when no input field is focused",
                            ],
                            'sw' => [
                                'title' => 'Njia za Mkato za Kibodi',
                                'excerpt' => 'Fanya kazi haraka na njia za mkato za kibodi.',
                                'content' => "# Njia za Mkato za Kibodi\n\nFanya kazi haraka na njia hizi za mkato.\n\n## Point of Sale\n\n| Mkato | Kitendo |\n|-------|--------|\n| `/` au `F2` | Zingatia upau wa utafutaji |\n| `Enter` | Maliza checkout |\n| `Esc` | Futa utafutaji / Funga dirisha |\n| `+` | Ongeza idadi ya bidhaa |\n| `-` | Punguza idadi ya bidhaa |\n\n## Urambazaji\n\n| Mkato | Kitendo |\n|-------|--------|\n| `Alt + D` | Nenda Dashibodi |\n| `Alt + P` | Nenda POS |\n| `Alt + I` | Nenda Stoku |\n| `Alt + R` | Nenda Ripoti |\n\n## Kwa Ujumla\n\n| Mkato | Kitendo |\n|-------|--------|\n| `Ctrl + S` | Hifadhi (kwenye fomu) |\n| `Esc` | Ghairi / Funga |\n\n## Vidokezo\n\n- Tumia mkato wa utafutaji kwa kutafuta bidhaa haraka\n- Zoezi njia za mkato kuharakisha checkout\n- Njia nyingi za mkato zinafanya kazi wakati hakuna sehemu ya kuingiza iliyozingatiwa",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'receipt-customization',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'Receipt Customization',
                                'excerpt' => 'Customize your receipts with business information.',
                                'content' => "# Receipt Customization\n\nCustomize receipts to reflect your brand.\n\n## Settings Location\n\n1. Go to **Settings**\n2. Find the Receipt section\n\n## Customizable Fields\n\n### Header\n- **Store Name**: Your business name\n- **Address**: Business address\n- **Phone**: Contact number\n- **Custom Header**: Additional text (promotions, tagline)\n\n### Footer\n- **Custom Footer**: Thank you message, return policy\n- **TIN Number**: Tax identification (if required)\n\n### Logo\n- Upload your business logo\n- Recommended size: 200x100 pixels\n- Supported formats: PNG, JPG\n\n## Receipt Information\n\nEach receipt automatically includes:\n- Receipt number\n- Date and time\n- Cashier name\n- Items purchased\n- Subtotal, tax, and total\n- Payment method\n- Change given (for cash)\n\n## Tips\n\n- Keep receipts clean and readable\n- Include return policy in footer\n- Test print after making changes\n- Consider adding your website or social media",
                            ],
                            'sw' => [
                                'title' => 'Kubinafsisha Risiti',
                                'excerpt' => 'Binafsisha risiti zako na habari za biashara.',
                                'content' => "# Kubinafsisha Risiti\n\nBinafsisha risiti kuonyesha chapa yako.\n\n## Mahali pa Mipangilio\n\n1. Nenda **Mipangilio**\n2. Tafuta sehemu ya Risiti\n\n## Sehemu Zinazoweza Kubadilishwa\n\n### Kichwa\n- **Jina la Duka**: Jina la biashara yako\n- **Anwani**: Anwani ya biashara\n- **Simu**: Nambari ya mawasiliano\n- **Kichwa Maalum**: Maandishi ya ziada (matangazo, kauli mbiu)\n\n### Chini\n- **Chini Maalum**: Ujumbe wa shukrani, sera ya kurejeshwa\n- **Nambari ya TIN**: Utambuzi wa kodi (ikihitajika)\n\n### Nembo\n- Pakia nembo ya biashara yako\n- Ukubwa unaopendekezwa: pikseli 200x100\n- Miundo inayoungwa mkono: PNG, JPG\n\n## Habari ya Risiti\n\nKila risiti inajumuisha kiotomatiki:\n- Nambari ya risiti\n- Tarehe na wakati\n- Jina la mkusanyaji\n- Vitu vilivyonunuliwa\n- Jumla ndogo, kodi, na jumla\n- Njia ya malipo\n- Chenji iliyotolewa (kwa pesa taslimu)\n\n## Vidokezo\n\n- Weka risiti safi na zinazosomeka\n- Jumuisha sera ya kurejeshwa chini\n- Chapisha jaribio baada ya kufanya mabadiliko\n- Fikiria kuongeza tovuti yako au mitandao ya kijamii",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'low-stock-alerts',
                        'sort_order' => 3,
                        'translations' => [
                            'en' => [
                                'title' => 'Low Stock Alerts',
                                'excerpt' => 'Get notified when products are running low.',
                                'content' => "# Low Stock Alerts\n\nNever run out of stock unexpectedly.\n\n## How It Works\n\nEach product has a low stock threshold. When inventory falls below this level, the product is flagged as \"low stock\".\n\n## Setting Thresholds\n\n### Global Default\n1. Go to **Settings**\n2. Set **Low Stock Threshold**\n3. This applies to new products\n\n### Per Product\n1. Go to **Products**\n2. Edit a product\n3. Set **Low Stock Threshold**\n4. Save\n\n## Viewing Low Stock\n\n### Dashboard\nThe dashboard shows products that need restocking.\n\n### Inventory Page\n1. Go to **Inventory**\n2. Filter by \"Low Stock\"\n3. See all products below threshold\n\n### Reports\nThe inventory report highlights low stock items.\n\n## Best Practices\n\n- Set realistic thresholds based on sales velocity\n- Higher threshold = earlier warning\n- Review and adjust thresholds quarterly\n- Popular items should have higher thresholds\n\n## Tips\n\n- Check low stock daily\n- Set up a reorder routine\n- Track lead times from suppliers",
                            ],
                            'sw' => [
                                'title' => 'Tahadhari za Stoku Chini',
                                'excerpt' => 'Pata arifa wakati bidhaa zinapungua.',
                                'content' => "# Tahadhari za Stoku Chini\n\nKamwe usimalize stoku bila kutarajia.\n\n## Jinsi Inavyofanya Kazi\n\nKila bidhaa ina kiwango cha stoku chini. Wakati stoku inashuka chini ya kiwango hiki, bidhaa inawekwa alama kama \"stoku chini\".\n\n## Kuweka Viwango\n\n### Chaguo-msingi la Jumla\n1. Nenda **Mipangilio**\n2. Weka **Kiwango cha Stoku Chini**\n3. Hii inatumika kwa bidhaa mpya\n\n### Kwa Bidhaa\n1. Nenda **Bidhaa**\n2. Hariri bidhaa\n3. Weka **Kiwango cha Stoku Chini**\n4. Hifadhi\n\n## Kuona Stoku Chini\n\n### Dashibodi\nDashibodi inaonyesha bidhaa zinazohitaji kujazwa upya.\n\n### Ukurasa wa Stoku\n1. Nenda **Stoku**\n2. Chuja kwa \"Stoku Chini\"\n3. Ona bidhaa zote chini ya kiwango\n\n### Ripoti\nRipoti ya stoku inaangazia vitu vya stoku chini.\n\n## Mazoea Bora\n\n- Weka viwango halisi kulingana na kasi ya mauzo\n- Kiwango cha juu = tahadhari ya mapema\n- Kagua na urekebisha viwango kila robo\n- Vitu maarufu vinapaswa kuwa na viwango vya juu\n\n## Vidokezo\n\n- Angalia stoku chini kila siku\n- Weka utaratibu wa kuagiza upya\n- Fuatilia nyakati za uongozaji kutoka kwa wasambazaji",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $existingCategory = DocumentationCategory::where('slug', $categoryData['slug'])->first();

            if ($existingCategory) {
                $this->command->info("Category '{$categoryData['slug']}' already exists, skipping...");
                continue;
            }

            $category = DocumentationCategory::create([
                'slug' => $categoryData['slug'],
                'icon' => $categoryData['icon'],
                'sort_order' => $categoryData['sort_order'],
                'is_active' => true,
            ]);

            foreach ($categoryData['translations'] as $locale => $translation) {
                $category->translations()->create([
                    'locale' => $locale,
                    'name' => $translation['name'],
                    'description' => $translation['description'],
                ]);
            }

            foreach ($categoryData['articles'] ?? [] as $articleData) {
                $article = DocumentationArticle::create([
                    'category_id' => $category->id,
                    'slug' => $articleData['slug'],
                    'sort_order' => $articleData['sort_order'],
                    'is_published' => true,
                    'is_featured' => $articleData['is_featured'] ?? false,
                ]);

                foreach ($articleData['translations'] as $locale => $translation) {
                    $article->translations()->create([
                        'locale' => $locale,
                        'title' => $translation['title'],
                        'excerpt' => $translation['excerpt'],
                        'content' => $translation['content'],
                    ]);
                }
            }

            $this->command->info("Created category '{$categoryData['slug']}' with " . count($categoryData['articles'] ?? []) . " articles");
        }
    }
}
