<?php
// Fake data for screen recordings: one made-up duka, never real customer data.
// Loaded by scripts/record.sh into a throwaway SQLite database.
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
mt_srand(7);
$n = now();
$ts = fn($d, $h = 12) => $n->copy()->subDays($d)->setTime($h, mt_rand(0, 59))->toDateTimeString();
$cid = DB::table('companies')->insertGetId(['name'=>'Duka la Mama Neema','email'=>'neema@demo.test','phone'=>'0712000111','address'=>'Kariakoo, Dar es Salaam','status'=>'approved','approved_at'=>$ts(40),'onboarding_completed'=>1,'user_limit'=>5,'created_at'=>$ts(40),'updated_at'=>$ts(40)]);
$uid = DB::table('users')->insertGetId(['name'=>'Neema Joseph','email'=>'neema@demo.test','password'=>Hash::make('demo1234'),'role'=>'company_owner','company_id'=>$cid,'email_verified_at'=>$ts(40),'is_active'=>1,'phone'=>'0712000111','created_at'=>$ts(40),'updated_at'=>$ts(40)]);
DB::table('mobile_app_requests')->insert(['company_id'=>$cid,'status'=>'approved','request_reason'=>'demo','expected_devices'=>2,'approved_at'=>$ts(30),'created_at'=>$ts(30),'updated_at'=>$ts(30)]);
$cats=[]; foreach(['Vinywaji','Vyakula','Sabuni na Usafi','Vitafunwa'] as $c) $cats[$c]=DB::table('categories')->insertGetId(['name'=>$c,'company_id'=>$cid,'created_at'=>$ts(40),'updated_at'=>$ts(40)]);
$prods=[ // name, cat, cost, price, stock, threshold
 ['Maji ya Kilimanjaro 1L','Vinywaji',600,1000,80,20],['Coca-Cola 500ml','Vinywaji',700,1200,60,20],['Azam Juice 1L','Vinywaji',1800,2500,25,10],
 ['Unga wa Sembe 2kg','Vyakula',3200,4000,40,15],['Mchele 1kg','Vyakula',2400,3000,50,20],['Sukari 1kg','Vyakula',2600,3200,6,15],
 ['Mafuta ya Kupikia 1L','Vyakula',4800,6000,30,10],['Chumvi 500g','Vyakula',500,800,35,10],['Tambi 400g','Vyakula',1200,1800,3,10],
 ['Sabuni ya Maji 1L','Sabuni na Usafi',2800,3800,20,8],['Sabuni ya Kipande','Sabuni na Usafi',900,1500,45,15],['Dawa ya Meno','Sabuni na Usafi',1500,2200,18,8],
 ['Biskuti Gloria','Vitafunwa',400,700,70,20],['Karanga 200g','Vitafunwa',700,1200,4,12],
];
$P=[]; foreach($prods as $i=>[$nm,$c,$cost,$pr,$st,$th]){
 $id=DB::table('products')->insertGetId(['name'=>$nm,'sku'=>'SKU'.($i+1),'barcode'=>'61000'.str_pad($i+1,4,'0',STR_PAD_LEFT),'category_id'=>$cats[$c],'cost_price'=>$cost,'selling_price'=>$pr,'tax_rate'=>0,'is_active'=>1,'company_id'=>$cid,'created_at'=>$ts(40),'updated_at'=>$ts(40)]);
 DB::table('inventory')->insert(['product_id'=>$id,'quantity'=>$st,'low_stock_threshold'=>$th,'company_id'=>$cid,'created_at'=>$ts(40),'updated_at'=>$ts(1)]);
 $P[]=['id'=>$id,'name'=>$nm,'cost'=>$cost,'price'=>$pr]; }
$custs=[]; foreach([['Baba Juma','0755111222',15000],['Mama Zawadi','0765333444',0],['Bi Halima','0713555666',8500]] as [$nm,$ph,$bal])
 $custs[]=DB::table('customers')->insertGetId(['company_id'=>$cid,'name'=>$nm,'phone'=>$ph,'credit_limit'=>50000,'current_balance'=>$bal,'is_active'=>1,'created_at'=>$ts(30),'updated_at'=>$ts(30)]);
$i=0; $methods=['cash','cash','cash','mobile','mobile','card'];
for($d=28;$d>=0;$d--){ $count=mt_rand(6,14)+(int)((28-$d)/4); if($d==0) $count=9;
 for($k=0;$k<$count;$k++){ $i++; $lines=[]; $sub=0; foreach((array) array_rand($P,mt_rand(2,4)) as $pi){ $p=$P[$pi]; $q=mt_rand(1,3); $lines[]=[$p,$q]; $sub+=$p['price']*$q; }
  $pm=$methods[array_rand($methods)]; $when=$ts($d,mt_rand(8,19));
  $t=DB::table('transactions')->insertGetId(['transaction_number'=>'TXN-'.str_pad($i,6,'0',STR_PAD_LEFT),'user_id'=>$uid,'company_id'=>$cid,'type'=>'sale','status'=>'completed','subtotal'=>$sub,'tax_amount'=>0,'discount_amount'=>0,'total'=>$sub,'payment_method'=>$pm,'amount_paid'=>ceil($sub/1000)*1000,'change_given'=>ceil($sub/1000)*1000-$sub,'created_at'=>$when,'updated_at'=>$when]);
  foreach($lines as [$p,$q]) DB::table('transaction_items')->insert(['company_id'=>$cid,'transaction_id'=>$t,'product_id'=>$p['id'],'product_name'=>$p['name'],'quantity'=>$q,'unit_price'=>$p['price'],'cost_price'=>$p['cost'],'tax_rate'=>0,'tax_amount'=>0,'subtotal'=>$p['price']*$q,'created_at'=>$when,'updated_at'=>$when]); } }
// open order
$o=DB::table('transactions')->insertGetId(['transaction_number'=>'ORD-000001','user_id'=>$uid,'company_id'=>$cid,'type'=>'order','status'=>'pending','customer_name'=>'Baba Juma','customer_phone'=>'0755111222','customer_id'=>$custs[0],'subtotal'=>22000,'tax_amount'=>0,'discount_amount'=>0,'total'=>22000,'payment_method'=>'cash','amount_paid'=>0,'change_given'=>0,'valid_until'=>$n->copy()->addDays(7)->toDateTimeString(),'created_at'=>$ts(1),'updated_at'=>$ts(1)]);
DB::table('transaction_items')->insert(['company_id'=>$cid,'transaction_id'=>$o,'product_id'=>$P[3]['id'],'product_name'=>$P[3]['name'],'quantity'=>5,'unit_price'=>4000,'cost_price'=>3200,'tax_rate'=>0,'tax_amount'=>0,'subtotal'=>20000,'created_at'=>$ts(1),'updated_at'=>$ts(1)]);
$ec=DB::table('expense_categories')->insertGetId(['company_id'=>$cid,'name'=>'Usafiri','is_active'=>1,'created_at'=>$ts(40),'updated_at'=>$ts(40)]);
foreach([[2,'Usafiri wa mizigo',15000],[5,'Umeme',32000],[9,'Mfuko wa plastiki',6000]] as [$d,$ds,$am]) DB::table('expenses')->insert(['company_id'=>$cid,'expense_category_id'=>$ec,'user_id'=>$uid,'description'=>$ds,'amount'=>$am,'quantity'=>1,'expense_date'=>$n->copy()->subDays($d)->toDateString(),'payment_method'=>'cash','created_at'=>$ts($d),'updated_at'=>$ts($d)]);
echo "seeded company $cid, $i sales\n";
