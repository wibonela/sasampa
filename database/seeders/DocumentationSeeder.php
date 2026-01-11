<?php

namespace Database\Seeders;

use App\Models\DocumentationArticle;
use App\Models\DocumentationCategory;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
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
                'slug' => 'faq',
                'icon' => 'bi-question-circle',
                'sort_order' => 5,
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
