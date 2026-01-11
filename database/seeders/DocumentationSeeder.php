<?php

namespace Database\Seeders;

use App\Models\DocumentationArticle;
use App\Models\DocumentationCategory;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing documentation to avoid duplicates
        DocumentationArticle::query()->delete();
        DocumentationCategory::query()->delete();

        $categories = [
            [
                'slug' => 'getting-started',
                'icon' => 'bi-rocket-takeoff',
                'sort_order' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Getting Started',
                        'description' => 'Learn the basics of Sasampa POS and get up and running quickly.',
                    ],
                    'sw' => [
                        'name' => 'Kuanza',
                        'description' => 'Jifunze misingi ya Sasampa POS na uanze haraka.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'welcome',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Welcome to Sasampa POS',
                                'excerpt' => 'Get started with your new point of sale system.',
                                'content' => "# Welcome to Sasampa POS\n\nSasampa POS is a modern, easy-to-use point of sale system designed for businesses in Tanzania and East Africa.\n\n## What You Can Do\n\n- **Manage Products**: Add, edit, and organize your products and categories\n- **Process Sales**: Quick and efficient checkout with receipt printing\n- **Track Inventory**: Monitor stock levels and get low stock alerts\n- **View Reports**: Analyze your sales data with detailed reports\n- **Manage Users**: Add staff members and control their access\n\n## Getting Help\n\nIf you need assistance, you can:\n\n1. Browse this documentation\n2. Contact our support team\n3. Use the feedback button in the app\n\nLet's get started!",
                            ],
                            'sw' => [
                                'title' => 'Karibu Sasampa POS',
                                'excerpt' => 'Anza na mfumo wako mpya wa mauzo.',
                                'content' => "# Karibu Sasampa POS\n\nSasampa POS ni mfumo wa kisasa na rahisi kutumia wa point of sale uliobuniwa kwa biashara nchini Tanzania na Afrika Mashariki.\n\n## Unachoweza Kufanya\n\n- **Simamia Bidhaa**: Ongeza, hariri, na panga bidhaa na makundi yako\n- **Fanya Mauzo**: Checkout ya haraka na ufanisi na uchapishaji risiti\n- **Fuatilia Stoku**: Fuatilia viwango vya stoku na pata tahadhari za stoku chini\n- **Tazama Ripoti**: Changanua data yako ya mauzo kwa ripoti za kina\n- **Simamia Watumiaji**: Ongeza wafanyakazi na dhibiti ufikiaji wao\n\n## Kupata Msaada\n\nUkihitaji msaada, unaweza:\n\n1. Pitia mwongozo huu\n2. Wasiliana na timu yetu ya msaada\n3. Tumia kitufe cha maoni kwenye programu\n\nTuanze!",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'first-login',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'Your First Login',
                                'excerpt' => 'Learn how to log in and navigate the dashboard.',
                                'content' => "# Your First Login\n\nAfter your company is approved, you can log in to Sasampa POS.\n\n## Logging In\n\n1. Go to the login page\n2. Enter your email address\n3. Enter your password\n4. Click \"Sign In\"\n\n## The Dashboard\n\nAfter logging in, you'll see your dashboard with:\n\n- **Today's Sales**: Your sales for today\n- **Quick Stats**: Key metrics at a glance\n- **Recent Transactions**: Your latest sales\n\n## Navigation\n\nUse the sidebar on the left to navigate:\n\n- **Dashboard**: Overview of your business\n- **Point of Sale**: Make sales\n- **Products**: Manage your inventory\n- **Reports**: View detailed analytics\n- **Settings**: Configure your account",
                            ],
                            'sw' => [
                                'title' => 'Kuingia Kwako kwa Mara ya Kwanza',
                                'excerpt' => 'Jifunze jinsi ya kuingia na kutembea dashibodi.',
                                'content' => "# Kuingia Kwako kwa Mara ya Kwanza\n\nBaada ya kampuni yako kuidhinishwa, unaweza kuingia Sasampa POS.\n\n## Kuingia\n\n1. Nenda ukurasa wa kuingia\n2. Ingiza anwani yako ya barua pepe\n3. Ingiza nenosiri lako\n4. Bonyeza \"Ingia\"\n\n## Dashibodi\n\nBaada ya kuingia, utaona dashibodi yako na:\n\n- **Mauzo ya Leo**: Mauzo yako ya leo\n- **Takwimu za Haraka**: Metriki muhimu kwa mtazamo\n- **Miamala ya Hivi Karibuni**: Mauzo yako ya hivi karibuni\n\n## Urambazaji\n\nTumia upau wa pembeni kushoto kutembea:\n\n- **Dashibodi**: Muhtasari wa biashara yako\n- **Point of Sale**: Fanya mauzo\n- **Bidhaa**: Simamia stoku yako\n- **Ripoti**: Tazama uchambuzi wa kina\n- **Mipangilio**: Sanidi akaunti yako",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'point-of-sale',
                'icon' => 'bi-cart3',
                'sort_order' => 2,
                'translations' => [
                    'en' => [
                        'name' => 'Point of Sale',
                        'description' => 'Learn how to process sales and manage transactions.',
                    ],
                    'sw' => [
                        'name' => 'Mauzo',
                        'description' => 'Jifunze jinsi ya kushughulikia mauzo na kusimamia miamala.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'making-sale',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Making a Sale',
                                'excerpt' => 'Step-by-step guide to processing a sale.',
                                'content' => "# Making a Sale\n\nProcessing sales in Sasampa POS is quick and easy.\n\n## Steps\n\n1. **Open POS**: Click on \"Point of Sale\" in the menu\n2. **Add Products**: Click on products to add them to the cart, or use the search bar\n3. **Adjust Quantities**: Use +/- buttons to change quantities\n4. **Enter Payment**: Enter the amount received from the customer\n5. **Complete Sale**: Click \"Checkout\" to complete the transaction\n6. **Print Receipt**: The receipt will be ready to print\n\n## Payment Methods\n\nSasampa POS supports:\n- Cash payments\n- Mobile money (coming soon)\n\n## Tips\n\n- Use the search bar to quickly find products\n- Check the cart before completing the sale\n- Always give the customer their receipt",
                            ],
                            'sw' => [
                                'title' => 'Kufanya Mauzo',
                                'excerpt' => 'Mwongozo wa hatua kwa hatua wa kushughulikia mauzo.',
                                'content' => "# Kufanya Mauzo\n\nKushughulikia mauzo katika Sasampa POS ni haraka na rahisi.\n\n## Hatua\n\n1. **Fungua POS**: Bonyeza \"Point of Sale\" kwenye menyu\n2. **Ongeza Bidhaa**: Bonyeza bidhaa kuziongeza kwenye kikapu, au tumia upau wa utafutaji\n3. **Rekebisha Idadi**: Tumia vitufe +/- kubadilisha idadi\n4. **Ingiza Malipo**: Ingiza kiasi kilichopokelewa kutoka kwa mteja\n5. **Maliza Mauzo**: Bonyeza \"Checkout\" kumaliza muamala\n6. **Chapisha Risiti**: Risiti itakuwa tayari kuchapishwa\n\n## Njia za Malipo\n\nSasampa POS inasaidia:\n- Malipo ya pesa taslimu\n- Pesa za simu (inakuja hivi karibuni)\n\n## Vidokezo\n\n- Tumia upau wa utafutaji kupata bidhaa haraka\n- Angalia kikapu kabla ya kumaliza mauzo\n- Daima mpe mteja risiti yake",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'voiding-transaction',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'Voiding a Transaction',
                                'excerpt' => 'Learn how to void a completed transaction.',
                                'content' => "# Voiding a Transaction\n\nSometimes you may need to void a transaction due to mistakes or returns.\n\n## How to Void\n\n1. Go to **Transactions** in the menu\n2. Find the transaction you want to void\n3. Click on the transaction to view details\n4. Click the **Void** button\n5. Confirm the void action\n\n## Important Notes\n\n- Only today's transactions can be voided\n- Voided transactions are marked but not deleted\n- Inventory is automatically restored when voiding\n- A reason should be noted for accounting purposes\n\n## When to Void\n\n- Customer changed their mind\n- Wrong items were scanned\n- Price errors discovered\n- Duplicate transactions",
                            ],
                            'sw' => [
                                'title' => 'Kubatilisha Muamala',
                                'excerpt' => 'Jifunze jinsi ya kubatilisha muamala uliokamilika.',
                                'content' => "# Kubatilisha Muamala\n\nWakati mwingine unaweza kuhitaji kubatilisha muamala kutokana na makosa au marejesho.\n\n## Jinsi ya Kubatilisha\n\n1. Nenda **Miamala** kwenye menyu\n2. Tafuta muamala unayotaka kubatilisha\n3. Bonyeza muamala kuona maelezo\n4. Bonyeza kitufe cha **Batilisha**\n5. Thibitisha kitendo cha kubatilisha\n\n## Maelezo Muhimu\n\n- Miamala ya leo tu inaweza kubatilishwa\n- Miamala iliyobatilishwa imewekwa alama lakini haijafutwa\n- Stoku inarejeshwa kiotomatiki wakati wa kubatilisha\n- Sababu inapaswa kuzingatiwa kwa madhumuni ya uhasibu\n\n## Wakati wa Kubatilisha\n\n- Mteja alibadili mawazo yake\n- Vitu vibaya viliskanwa\n- Makosa ya bei yaligunduliwa\n- Miamala ya marudio",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'products-inventory',
                'icon' => 'bi-box-seam',
                'sort_order' => 3,
                'translations' => [
                    'en' => [
                        'name' => 'Products & Inventory',
                        'description' => 'Manage your products, categories, and stock levels.',
                    ],
                    'sw' => [
                        'name' => 'Bidhaa na Stoku',
                        'description' => 'Simamia bidhaa zako, makundi, na viwango vya stoku.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'adding-products',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Adding Products',
                                'excerpt' => 'Learn how to add new products to your inventory.',
                                'content' => "# Adding Products\n\nProducts are the items you sell. Here's how to add them.\n\n## Steps to Add a Product\n\n1. Go to **Products** in the menu\n2. Click **Add Product**\n3. Fill in the product details:\n   - **Name**: The product name\n   - **Category**: Select a category\n   - **Price**: The selling price\n   - **Cost**: Your cost price (optional)\n   - **Stock**: Initial quantity\n4. Click **Save**\n\n## Product Fields\n\n| Field | Required | Description |\n|-------|----------|-------------|\n| Name | Yes | Product display name |\n| Category | Yes | For organization |\n| Price | Yes | Selling price |\n| SKU | No | Stock keeping unit |\n| Cost | No | Your purchase cost |\n| Stock | No | Current quantity |\n\n## Tips\n\n- Use clear, descriptive names\n- Set up categories first for better organization\n- Keep stock quantities accurate",
                            ],
                            'sw' => [
                                'title' => 'Kuongeza Bidhaa',
                                'excerpt' => 'Jifunze jinsi ya kuongeza bidhaa mpya kwenye stoku yako.',
                                'content' => "# Kuongeza Bidhaa\n\nBidhaa ni vitu unavyouza. Hivi ndivyo unavyozongeza.\n\n## Hatua za Kuongeza Bidhaa\n\n1. Nenda **Bidhaa** kwenye menyu\n2. Bonyeza **Ongeza Bidhaa**\n3. Jaza maelezo ya bidhaa:\n   - **Jina**: Jina la bidhaa\n   - **Kundi**: Chagua kundi\n   - **Bei**: Bei ya kuuzia\n   - **Gharama**: Bei yako ya kununua (si lazima)\n   - **Stoku**: Idadi ya awali\n4. Bonyeza **Hifadhi**\n\n## Sehemu za Bidhaa\n\n| Sehemu | Lazima | Maelezo |\n|--------|--------|----------|\n| Jina | Ndiyo | Jina la kuonyesha bidhaa |\n| Kundi | Ndiyo | Kwa mpangilio |\n| Bei | Ndiyo | Bei ya kuuzia |\n| SKU | Hapana | Kipimo cha stoku |\n| Gharama | Hapana | Gharama yako ya kununua |\n| Stoku | Hapana | Idadi ya sasa |\n\n## Vidokezo\n\n- Tumia majina wazi na ya kuelezea\n- Weka makundi kwanza kwa mpangilio bora\n- Weka idadi za stoku sahihi",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'managing-stock',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'Managing Stock',
                                'excerpt' => 'Keep your inventory accurate with stock adjustments.',
                                'content' => "# Managing Stock\n\nAccurate stock levels are essential for your business.\n\n## Stock Adjustments\n\nTo adjust stock levels:\n\n1. Go to **Stock** in the menu\n2. Find the product\n3. Click **Adjust**\n4. Enter the adjustment:\n   - Positive number to add stock\n   - Negative number to remove stock\n5. Add a reason for the adjustment\n6. Click **Save**\n\n## Adjustment Reasons\n\n- **Restock**: New inventory received\n- **Damaged**: Items damaged or broken\n- **Theft/Loss**: Stolen or lost items\n- **Count Correction**: Physical count differs\n- **Return to Supplier**: Returned items\n\n## Stock History\n\nView all stock changes in the history section to track adjustments over time.",
                            ],
                            'sw' => [
                                'title' => 'Kusimamia Stoku',
                                'excerpt' => 'Weka stoku yako sahihi na marekebisho ya stoku.',
                                'content' => "# Kusimamia Stoku\n\nViwango sahihi vya stoku ni muhimu kwa biashara yako.\n\n## Marekebisho ya Stoku\n\nKurekebisha viwango vya stoku:\n\n1. Nenda **Stoku** kwenye menyu\n2. Tafuta bidhaa\n3. Bonyeza **Rekebisha**\n4. Ingiza marekebisho:\n   - Nambari chanya kuongeza stoku\n   - Nambari hasi kuondoa stoku\n5. Ongeza sababu ya marekebisho\n6. Bonyeza **Hifadhi**\n\n## Sababu za Marekebisho\n\n- **Kujaza upya**: Stoku mpya imepokelewa\n- **Kuharibiwa**: Vitu vilivyoharibiwa au kuvunjika\n- **Wizi/Kupoteza**: Vitu vilivyoibwa au kupotea\n- **Urekebishaji wa Hesabu**: Hesabu halisi inatofautiana\n- **Kurejeshwa kwa Msambazaji**: Vitu vilivyorejeshwa\n\n## Historia ya Stoku\n\nTazama mabadiliko yote ya stoku katika sehemu ya historia kufuatilia marekebisho kwa wakati.",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'reports',
                'icon' => 'bi-bar-chart',
                'sort_order' => 4,
                'translations' => [
                    'en' => [
                        'name' => 'Reports',
                        'description' => 'Understand your business with detailed reports.',
                    ],
                    'sw' => [
                        'name' => 'Ripoti',
                        'description' => 'Elewa biashara yako na ripoti za kina.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'sales-reports',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Sales Reports',
                                'excerpt' => 'Analyze your sales data with detailed reports.',
                                'content' => "# Sales Reports\n\nUnderstand your sales performance with comprehensive reports.\n\n## Available Reports\n\n### Daily Sales\nView sales totals for each day, including:\n- Total revenue\n- Number of transactions\n- Average transaction value\n\n### Product Sales\nSee which products sell best:\n- Units sold\n- Revenue per product\n- Profit margins\n\n### Time Analysis\nUnderstand peak sales times:\n- Hourly breakdown\n- Day of week trends\n- Seasonal patterns\n\n## Using Filters\n\n- **Date Range**: Select specific periods\n- **Category**: Filter by product category\n- **Payment Method**: Filter by how customers paid\n\n## Exporting\n\nExport reports to PDF or CSV for:\n- Accounting purposes\n- Business planning\n- Team sharing",
                            ],
                            'sw' => [
                                'title' => 'Ripoti za Mauzo',
                                'excerpt' => 'Changanua data yako ya mauzo na ripoti za kina.',
                                'content' => "# Ripoti za Mauzo\n\nElewa utendaji wako wa mauzo na ripoti za kina.\n\n## Ripoti Zinazopatikana\n\n### Mauzo ya Kila Siku\nTazama jumla ya mauzo kwa kila siku, ikiwa ni pamoja na:\n- Mapato ya jumla\n- Idadi ya miamala\n- Thamani ya wastani ya muamala\n\n### Mauzo ya Bidhaa\nOna bidhaa zipi zinazouzwa zaidi:\n- Vitengo vilivyouzwa\n- Mapato kwa bidhaa\n- Margin za faida\n\n### Uchambuzi wa Wakati\nElewa wakati wa mauzo mengi:\n- Mgawanyo wa kila saa\n- Mwenendo wa siku ya wiki\n- Mifumo ya msimu\n\n## Kutumia Vichujio\n\n- **Kipindi cha Tarehe**: Chagua vipindi maalum\n- **Kundi**: Chuja kwa kundi la bidhaa\n- **Njia ya Malipo**: Chuja kwa jinsi wateja walivyolipa\n\n## Kuhamisha\n\nHamisha ripoti kwa PDF au CSV kwa:\n- Madhumuni ya uhasibu\n- Mipango ya biashara\n- Kushiriki na timu",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'user-management',
                'icon' => 'bi-people',
                'sort_order' => 5,
                'translations' => [
                    'en' => [
                        'name' => 'User Management',
                        'description' => 'Add staff members and manage their access and permissions.',
                    ],
                    'sw' => [
                        'name' => 'Usimamizi wa Watumiaji',
                        'description' => 'Ongeza wafanyakazi na simamia ufikiaji na ruhusa zao.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'adding-staff',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Adding Staff Members',
                                'excerpt' => 'Learn how to add and invite staff to your team.',
                                'content' => "# Adding Staff Members\n\nAdd cashiers and other staff members to help run your business.\n\n## User Limits\n\nYour account has a user limit (default: 3 users). If you need more:\n1. Go to **Staff** in the menu\n2. Click **Request More** when at your limit\n3. Fill in how many users you need and why\n4. Wait for admin approval\n\n## Steps to Add Staff\n\n1. Go to **Staff** in the menu\n2. Click **Add Staff**\n3. Fill in their details:\n   - **Name**: Staff member's name\n   - **Email**: Their email address\n   - **Role**: Usually Cashier\n   - **Branches**: Which branches they can access\n4. Choose invitation method:\n   - **Email**: They receive a link to set password\n   - **PIN**: You set a 4-digit PIN for quick login\n   - **Both**: Email link + PIN access\n5. Set their permissions\n6. Click **Save**\n\n## Invitation Methods\n\n| Method | Best For | How It Works |\n|--------|----------|-------------|\n| Email | Full access users | Receives email to set password |\n| PIN | Quick POS access | Uses PIN to login at POS |\n| Both | Flexibility | Can use either method |\n\n## Tips\n\n- Use PIN for cashiers who share devices\n- Use email for managers who need full access\n- Always set appropriate permissions",
                            ],
                            'sw' => [
                                'title' => 'Kuongeza Wafanyakazi',
                                'excerpt' => 'Jifunze jinsi ya kuongeza na kuwaalika wafanyakazi kwenye timu yako.',
                                'content' => "# Kuongeza Wafanyakazi\n\nOngeza wakusanyaji fedha na wafanyakazi wengine kusaidia kuendesha biashara yako.\n\n## Vikomo vya Watumiaji\n\nAkaunti yako ina kikomo cha watumiaji (kawaida: watumiaji 3). Ukihitaji zaidi:\n1. Nenda **Wafanyakazi** kwenye menyu\n2. Bonyeza **Omba Zaidi** unapofikia kikomo chako\n3. Jaza ni watumiaji wangapi unahitaji na kwa nini\n4. Subiri idhini ya msimamizi\n\n## Hatua za Kuongeza Mfanyakazi\n\n1. Nenda **Wafanyakazi** kwenye menyu\n2. Bonyeza **Ongeza Mfanyakazi**\n3. Jaza maelezo yao:\n   - **Jina**: Jina la mfanyakazi\n   - **Barua pepe**: Anwani yao ya barua pepe\n   - **Jukumu**: Kawaida Mkusanyaji Fedha\n   - **Matawi**: Matawi gani wanaweza kufikia\n4. Chagua njia ya mwaliko:\n   - **Barua pepe**: Wanapokea kiungo cha kuweka nenosiri\n   - **PIN**: Unaweka PIN ya tarakimu 4 kwa kuingia haraka\n   - **Zote mbili**: Kiungo cha barua pepe + ufikiaji wa PIN\n5. Weka ruhusa zao\n6. Bonyeza **Hifadhi**\n\n## Njia za Mwaliko\n\n| Njia | Bora Kwa | Inavyofanya Kazi |\n|------|----------|------------------|\n| Barua pepe | Watumiaji wenye ufikiaji kamili | Wanapokea barua pepe kuweka nenosiri |\n| PIN | Ufikiaji wa haraka wa POS | Hutumia PIN kuingia POS |\n| Zote mbili | Unyumbufu | Wanaweza kutumia njia yoyote |\n\n## Vidokezo\n\n- Tumia PIN kwa wakusanyaji fedha wanaoshiriki vifaa\n- Tumia barua pepe kwa wasimamizi wanaohitaji ufikiaji kamili\n- Daima weka ruhusa zinazofaa",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'permissions',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'Managing Permissions',
                                'excerpt' => 'Control what each staff member can do.',
                                'content' => "# Managing Permissions\n\nControl exactly what each staff member can access and do.\n\n## Available Permissions\n\n| Permission | What It Allows |\n|------------|---------------|\n| View Reports | Access sales and inventory reports |\n| Manage Inventory | Adjust stock levels |\n| Void Transactions | Cancel completed sales |\n| Apply Discounts | Give discounts on sales |\n| View Cost Prices | See product cost prices |\n| Manage Users | Add and edit staff members |\n| Manage Branches | Configure branch settings |\n| Manage Settings | Change company settings |\n\n## Setting Permissions\n\n1. Go to **Staff** in the menu\n2. Click the shield icon next to a staff member\n3. Check/uncheck permissions\n4. Click **Save**\n\n## Permission Groups\n\n### Cashier (Basic)\n- Process sales\n- View own transactions\n\n### Senior Cashier\n- All basic permissions\n- Void transactions\n- Apply discounts\n\n### Manager\n- All permissions except settings\n\n### Company Owner\n- Full access to everything\n\n## Tips\n\n- Start with minimal permissions\n- Add permissions as needed\n- Review permissions regularly",
                            ],
                            'sw' => [
                                'title' => 'Kusimamia Ruhusa',
                                'excerpt' => 'Dhibiti kila mfanyakazi anaweza kufanya nini.',
                                'content' => "# Kusimamia Ruhusa\n\nDhibiti kwa usahihi kila mfanyakazi anaweza kufikia na kufanya nini.\n\n## Ruhusa Zinazopatikana\n\n| Ruhusa | Inaruhusu Nini |\n|--------|----------------|\n| Tazama Ripoti | Ufikiaji wa ripoti za mauzo na stoku |\n| Simamia Stoku | Rekebisha viwango vya stoku |\n| Batilisha Miamala | Ghairi mauzo yaliyokamilika |\n| Toa Punguzo | Toa punguzo kwenye mauzo |\n| Tazama Bei za Gharama | Ona bei za gharama za bidhaa |\n| Simamia Watumiaji | Ongeza na hariri wafanyakazi |\n| Simamia Matawi | Sanidi mipangilio ya tawi |\n| Simamia Mipangilio | Badilisha mipangilio ya kampuni |\n\n## Kuweka Ruhusa\n\n1. Nenda **Wafanyakazi** kwenye menyu\n2. Bonyeza ikoni ya ngao karibu na mfanyakazi\n3. Weka/ondoa alama kwenye ruhusa\n4. Bonyeza **Hifadhi**\n\n## Makundi ya Ruhusa\n\n### Mkusanyaji Fedha (Msingi)\n- Fanya mauzo\n- Tazama miamala yako\n\n### Mkusanyaji Fedha Mkuu\n- Ruhusa zote za msingi\n- Batilisha miamala\n- Toa punguzo\n\n### Meneja\n- Ruhusa zote isipokuwa mipangilio\n\n### Mmiliki wa Kampuni\n- Ufikiaji kamili wa kila kitu\n\n## Vidokezo\n\n- Anza na ruhusa ndogo\n- Ongeza ruhusa kadri inavyohitajika\n- Pitia ruhusa mara kwa mara",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'pin-login',
                        'sort_order' => 3,
                        'translations' => [
                            'en' => [
                                'title' => 'PIN Login System',
                                'excerpt' => 'Quick login for POS using PIN codes.',
                                'content' => "# PIN Login System\n\nThe PIN system allows quick cashier switching at the POS.\n\n## How PIN Login Works\n\n1. Staff member enters their 4-digit PIN\n2. System verifies the PIN\n3. Staff is logged in and ready to sell\n\n## Setting Up PIN\n\nWhen creating a staff member:\n1. Choose **PIN** or **Both** as invitation method\n2. Enter a 4-digit PIN\n3. Share the PIN securely with the staff member\n\n## Resetting PIN\n\n1. Go to **Staff**\n2. Click **Edit** on the staff member\n3. Click **Reset PIN**\n4. Share the new PIN with them\n\n## Quick Switch Mode\n\nOn shared POS terminals:\n1. Current cashier logs out\n2. New cashier enters their PIN\n3. Instantly switches to their session\n\n## Security Tips\n\n- Don't share PINs between staff\n- Change PINs if compromised\n- Use unique PINs (avoid 1234, 0000)\n- Deactivate staff who leave",
                            ],
                            'sw' => [
                                'title' => 'Mfumo wa Kuingia kwa PIN',
                                'excerpt' => 'Kuingia haraka kwa POS kwa kutumia misimbo ya PIN.',
                                'content' => "# Mfumo wa Kuingia kwa PIN\n\nMfumo wa PIN unaruhusu kubadilisha mkusanyaji fedha haraka kwenye POS.\n\n## Jinsi Kuingia kwa PIN Inavyofanya Kazi\n\n1. Mfanyakazi anaingiza PIN yake ya tarakimu 4\n2. Mfumo unathibitisha PIN\n3. Mfanyakazi ameingia na yuko tayari kuuza\n\n## Kuweka PIN\n\nWakati wa kuunda mfanyakazi:\n1. Chagua **PIN** au **Zote mbili** kama njia ya mwaliko\n2. Ingiza PIN ya tarakimu 4\n3. Shiriki PIN kwa usalama na mfanyakazi\n\n## Kuweka Upya PIN\n\n1. Nenda **Wafanyakazi**\n2. Bonyeza **Hariri** kwa mfanyakazi\n3. Bonyeza **Weka Upya PIN**\n4. Shiriki PIN mpya naye\n\n## Hali ya Kubadilisha Haraka\n\nKwenye vituo vya POS vinavyoshirikiwa:\n1. Mkusanyaji fedha wa sasa anatoka\n2. Mkusanyaji fedha mpya anaingiza PIN yake\n3. Mara moja anabadilika kwenye kikao chake\n\n## Vidokezo vya Usalama\n\n- Usishiriki PIN kati ya wafanyakazi\n- Badilisha PIN zikiharibiwa\n- Tumia PIN za kipekee (epuka 1234, 0000)\n- Zima akaunti za wafanyakazi wanaoondoka",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'branches',
                'icon' => 'bi-building',
                'sort_order' => 6,
                'translations' => [
                    'en' => [
                        'name' => 'Branch Management',
                        'description' => 'Manage multiple business locations.',
                    ],
                    'sw' => [
                        'name' => 'Usimamizi wa Matawi',
                        'description' => 'Simamia maeneo mengi ya biashara.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'multi-branch-setup',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Multi-Branch Setup',
                                'excerpt' => 'Set up and manage multiple business locations.',
                                'content' => "# Multi-Branch Setup\n\nExpand your business with multiple branches.\n\n## Creating a Branch\n\n1. Go to **Branches** in the menu\n2. Click **Add Branch**\n3. Fill in:\n   - **Name**: Branch name (e.g., \"Downtown Store\")\n   - **Code**: Short code (e.g., \"DT\")\n   - **Address**: Branch location\n   - **Phone**: Contact number\n4. Click **Save**\n\n## Main Branch\n\nYour first branch is the \"Main Branch\". It:\n- Cannot be deleted\n- Is the default for new products\n- Contains your primary data\n\n## Branch Settings\n\nEach branch can have:\n- Its own staff members\n- Separate inventory\n- Individual reports\n\n## Assigning Staff to Branches\n\n1. Go to **Branches**\n2. Click **Users** on a branch\n3. Add staff members to that branch\n4. Set their default branch\n\n## Switching Branches\n\nUse the branch switcher in the sidebar to:\n- View different branch data\n- Process sales for specific branches\n- Check branch-specific reports",
                            ],
                            'sw' => [
                                'title' => 'Kuweka Matawi Mengi',
                                'excerpt' => 'Weka na simamia maeneo mengi ya biashara.',
                                'content' => "# Kuweka Matawi Mengi\n\nPanua biashara yako na matawi mengi.\n\n## Kuunda Tawi\n\n1. Nenda **Matawi** kwenye menyu\n2. Bonyeza **Ongeza Tawi**\n3. Jaza:\n   - **Jina**: Jina la tawi (mfano, \"Duka la Mjini\")\n   - **Msimbo**: Msimbo mfupi (mfano, \"MJ\")\n   - **Anwani**: Mahali pa tawi\n   - **Simu**: Nambari ya mawasiliano\n4. Bonyeza **Hifadhi**\n\n## Tawi Kuu\n\nTawi lako la kwanza ni \"Tawi Kuu\". Linaweza:\n- Haliwezi kufutwa\n- Ni chaguo-msingi kwa bidhaa mpya\n- Lina data yako ya msingi\n\n## Mipangilio ya Tawi\n\nKila tawi linaweza kuwa na:\n- Wafanyakazi wake\n- Stoku tofauti\n- Ripoti za kibinafsi\n\n## Kuwapa Wafanyakazi Matawi\n\n1. Nenda **Matawi**\n2. Bonyeza **Watumiaji** kwenye tawi\n3. Ongeza wafanyakazi kwenye tawi hilo\n4. Weka tawi lao la kawaida\n\n## Kubadilisha Matawi\n\nTumia kibadilishi cha tawi kwenye upau wa pembeni:\n- Tazama data ya matawi tofauti\n- Fanya mauzo kwa matawi maalum\n- Angalia ripoti za tawi maalum",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'branch-reports',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'Branch Reports',
                                'excerpt' => 'View reports for individual branches.',
                                'content' => "# Branch Reports\n\nAnalyze performance across your branches.\n\n## Viewing Branch Data\n\n### As Company Owner\nCompany owners see combined data from ALL branches:\n- Total sales across all locations\n- Combined inventory\n- Unified transaction history\n\n### As Staff Member\nStaff see only their assigned branch data:\n- Sales from their branch\n- Branch inventory\n- Their transactions\n\n## Dashboard Overview\n\nThe dashboard shows:\n- **Owners**: All branch data combined\n- **Staff**: Current branch data only\n\n## Filtering by Branch\n\nIn reports, you can:\n1. Select a specific branch\n2. Compare branch performance\n3. Export branch-specific data\n\n## Best Practices\n\n- Review branch performance weekly\n- Compare sales across locations\n- Identify top-performing branches\n- Address underperforming locations",
                            ],
                            'sw' => [
                                'title' => 'Ripoti za Tawi',
                                'excerpt' => 'Tazama ripoti za matawi ya kibinafsi.',
                                'content' => "# Ripoti za Tawi\n\nChanganua utendaji kwenye matawi yako.\n\n## Kutazama Data ya Tawi\n\n### Kama Mmiliki wa Kampuni\nWamiliki wa kampuni wanaona data iliyounganishwa kutoka matawi YOTE:\n- Jumla ya mauzo kwenye maeneo yote\n- Stoku iliyounganishwa\n- Historia ya miamala iliyounganishwa\n\n### Kama Mfanyakazi\nWafanyakazi wanaona data ya tawi lao tu:\n- Mauzo kutoka tawi lao\n- Stoku ya tawi\n- Miamala yao\n\n## Muhtasari wa Dashibodi\n\nDashibodi inaonyesha:\n- **Wamiliki**: Data yote ya matawi iliyounganishwa\n- **Wafanyakazi**: Data ya tawi la sasa tu\n\n## Kuchuja kwa Tawi\n\nKatika ripoti, unaweza:\n1. Chagua tawi maalum\n2. Linganisha utendaji wa matawi\n3. Hamisha data ya tawi maalum\n\n## Mazoea Bora\n\n- Pitia utendaji wa tawi kila wiki\n- Linganisha mauzo kwenye maeneo\n- Tambua matawi yanayofanya vizuri\n- Shughulikia maeneo yanayofanya vibaya",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'settings',
                'icon' => 'bi-gear',
                'sort_order' => 7,
                'translations' => [
                    'en' => [
                        'name' => 'Settings',
                        'description' => 'Configure your business settings and preferences.',
                    ],
                    'sw' => [
                        'name' => 'Mipangilio',
                        'description' => 'Sanidi mipangilio na mapendekezo ya biashara yako.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'company-settings',
                        'sort_order' => 1,
                        'is_featured' => true,
                        'translations' => [
                            'en' => [
                                'title' => 'Company Settings',
                                'excerpt' => 'Configure your company profile and preferences.',
                                'content' => "# Company Settings\n\nCustomize Sasampa POS for your business.\n\n## Accessing Settings\n\n1. Click **Settings** in the menu\n2. Only company owners can access settings\n\n## Company Profile\n\nUpdate your business information:\n- **Company Name**: Your business name\n- **Email**: Contact email\n- **Phone**: Business phone number\n- **Address**: Business address\n- **Logo**: Upload your logo (appears on receipts)\n\n## Receipt Settings\n\nCustomize your receipts:\n- Add company logo\n- Include address and phone\n- Add custom footer message\n- Show/hide tax information\n\n## Tax Settings\n\nConfigure tax:\n- Enable/disable tax\n- Set tax percentage\n- Include tax in prices or add separately\n\n## Currency\n\nThe system uses Tanzanian Shillings (TZS) by default.\n\n## Tips\n\n- Keep your logo simple for better printing\n- Update contact info when it changes\n- Test receipt layout after changes",
                            ],
                            'sw' => [
                                'title' => 'Mipangilio ya Kampuni',
                                'excerpt' => 'Sanidi wasifu wa kampuni yako na mapendekezo.',
                                'content' => "# Mipangilio ya Kampuni\n\nBinafsisha Sasampa POS kwa biashara yako.\n\n## Kufikia Mipangilio\n\n1. Bonyeza **Mipangilio** kwenye menyu\n2. Wamiliki wa kampuni tu wanaweza kufikia mipangilio\n\n## Wasifu wa Kampuni\n\nSasisha taarifa za biashara yako:\n- **Jina la Kampuni**: Jina la biashara yako\n- **Barua pepe**: Barua pepe ya mawasiliano\n- **Simu**: Nambari ya simu ya biashara\n- **Anwani**: Anwani ya biashara\n- **Nembo**: Pakia nembo yako (inaonekana kwenye risiti)\n\n## Mipangilio ya Risiti\n\nBinafsisha risiti zako:\n- Ongeza nembo ya kampuni\n- Jumuisha anwani na simu\n- Ongeza ujumbe wa chini wa kawaida\n- Onyesha/ficha taarifa za kodi\n\n## Mipangilio ya Kodi\n\nSanidi kodi:\n- Wezesha/zima kodi\n- Weka asilimia ya kodi\n- Jumuisha kodi katika bei au ongeza tofauti\n\n## Sarafu\n\nMfumo unatumia Shilingi za Tanzania (TZS) kwa chaguo-msingi.\n\n## Vidokezo\n\n- Weka nembo yako rahisi kwa uchapishaji bora\n- Sasisha taarifa za mawasiliano zinapobadilika\n- Jaribu mpangilio wa risiti baada ya mabadiliko",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'faq',
                'icon' => 'bi-question-circle',
                'sort_order' => 8,
                'translations' => [
                    'en' => [
                        'name' => 'FAQ & Troubleshooting',
                        'description' => 'Common questions and solutions to problems.',
                    ],
                    'sw' => [
                        'name' => 'Maswali na Ufumbuzi',
                        'description' => 'Maswali ya kawaida na ufumbuzi wa matatizo.',
                    ],
                ],
                'articles' => [
                    [
                        'slug' => 'common-questions',
                        'sort_order' => 1,
                        'translations' => [
                            'en' => [
                                'title' => 'Common Questions',
                                'excerpt' => 'Answers to frequently asked questions.',
                                'content' => "# Common Questions\n\n## Account & Access\n\n**How do I reset my password?**\nClick \"Forgot Password\" on the login page and enter your email. You'll receive a link to reset your password.\n\n**Can I add more users?**\nYes! Go to Settings and add new users. You can assign them as cashiers with limited access.\n\n## Sales & Transactions\n\n**Can I edit a completed sale?**\nCompleted sales cannot be edited. You can void the transaction and create a new one.\n\n**How do I print receipts?**\nAfter completing a sale, click the print button. Make sure your receipt printer is connected.\n\n## Products\n\n**How do I import products?**\nCurrently, products must be added manually. Bulk import is coming soon.\n\n**Can I have the same product at different prices?**\nCreate variations of the product with different names and prices.\n\n## Support\n\n**How do I contact support?**\nUse the feedback button in the app or email our support team.",
                            ],
                            'sw' => [
                                'title' => 'Maswali ya Kawaida',
                                'excerpt' => 'Majibu ya maswali yanayoulizwa mara kwa mara.',
                                'content' => "# Maswali ya Kawaida\n\n## Akaunti na Ufikiaji\n\n**Ninawezaje kuweka upya nenosiri langu?**\nBonyeza \"Umesahau Nenosiri\" kwenye ukurasa wa kuingia na ingiza barua pepe yako. Utapokea kiungo cha kuweka upya nenosiri lako.\n\n**Ninaweza kuongeza watumiaji zaidi?**\nNdiyo! Nenda Mipangilio na uongeze watumiaji wapya. Unaweza kuwapa kama wakusanyaji wa fedha wenye ufikiaji mdogo.\n\n## Mauzo na Miamala\n\n**Ninaweza kuhariri mauzo yaliyokamilika?**\nMauzo yaliyokamilika hayawezi kuhaririwa. Unaweza kubatilisha muamala na kuunda mpya.\n\n**Ninawezaje kuchapisha risiti?**\nBaada ya kumaliza mauzo, bonyeza kitufe cha kuchapisha. Hakikisha printa yako ya risiti imeunganishwa.\n\n## Bidhaa\n\n**Ninawezaje kuingiza bidhaa?**\nKwa sasa, bidhaa lazima ziongezwe kwa mkono. Uingizaji wa wingi unakuja hivi karibuni.\n\n**Ninaweza kuwa na bidhaa moja kwa bei tofauti?**\nTengeneza tofauti za bidhaa na majina na bei tofauti.\n\n## Msaada\n\n**Ninawezaje kuwasiliana na msaada?**\nTumia kitufe cha maoni kwenye programu au tuma barua pepe kwa timu yetu ya msaada.",
                            ],
                        ],
                    ],
                    [
                        'slug' => 'troubleshooting',
                        'sort_order' => 2,
                        'translations' => [
                            'en' => [
                                'title' => 'Troubleshooting',
                                'excerpt' => 'Solutions to common problems.',
                                'content' => "# Troubleshooting\n\n## Login Issues\n\n**I can't log in**\n1. Check your email and password are correct\n2. Make sure Caps Lock is off\n3. Try resetting your password\n4. Clear your browser cache\n\n**Session expired**\nFor security, sessions expire after inactivity. Simply log in again.\n\n## Display Issues\n\n**Page looks broken**\n1. Refresh the page (Ctrl+R or Cmd+R)\n2. Clear browser cache\n3. Try a different browser\n\n**Mobile display problems**\nSasampa POS works best on tablets and desktops. Some features may be limited on small phones.\n\n## Printing Issues\n\n**Receipt won't print**\n1. Check printer is on and connected\n2. Check printer has paper\n3. Try printing a test page from your computer\n4. Restart the browser\n\n## Data Issues\n\n**Products not showing**\n1. Check you're viewing the correct category\n2. Refresh the page\n3. Check products are marked as active\n\n## Still Need Help?\n\nContact our support team for assistance.",
                            ],
                            'sw' => [
                                'title' => 'Utatuzi wa Matatizo',
                                'excerpt' => 'Ufumbuzi wa matatizo ya kawaida.',
                                'content' => "# Utatuzi wa Matatizo\n\n## Matatizo ya Kuingia\n\n**Siwezi kuingia**\n1. Angalia barua pepe na nenosiri lako ni sahihi\n2. Hakikisha Caps Lock imezimwa\n3. Jaribu kuweka upya nenosiri lako\n4. Futa kashe ya kivinjari\n\n**Kikao kimeisha**\nKwa usalama, vikao vinaisha baada ya kutokuwa hai. Ingia tena tu.\n\n## Matatizo ya Kuonyesha\n\n**Ukurasa unaonekana kuharibika**\n1. Onyesha upya ukurasa (Ctrl+R au Cmd+R)\n2. Futa kashe ya kivinjari\n3. Jaribu kivinjari tofauti\n\n**Matatizo ya kuonyesha simu**\nSasampa POS inafanya kazi vizuri kwenye vidonge na kompyuta za mezani. Baadhi ya vipengele vinaweza kuwa na vikwazo kwenye simu ndogo.\n\n## Matatizo ya Uchapishaji\n\n**Risiti haitachapishwa**\n1. Angalia printa imewashwa na kuunganishwa\n2. Angalia printa ina karatasi\n3. Jaribu kuchapisha ukurasa wa jaribio kutoka kompyuta yako\n4. Anzisha upya kivinjari\n\n## Matatizo ya Data\n\n**Bidhaa hazionyeshwi**\n1. Angalia unaona kundi sahihi\n2. Onyesha upya ukurasa\n3. Angalia bidhaa zimewekwa alama kama hai\n\n## Bado Unahitaji Msaada?\n\nWasiliana na timu yetu ya msaada kwa usaidizi.",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
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
        }
    }
}
