# Sasampa POS: Launch video (Kiswahili captions, English voiceover, ~80s)

The video is built from a real screen recording of the app, so every screen you see is the real app.
Captions use the app's own Swahili wording (`mobile_app/lib/l10n/app_sw.arb`). A Swahili speaker should review every line.
Voiceover is English (generated); the English lines are in `data/launch.json` under `vo`.

| # | Seconds | What is on screen (real app) | Caption |
|---|---------|------------------------------|---------|
| intro | 5 | Animated title card | "Bado unaandika mauzo kwenye daftari?" / "Kuna njia rahisi zaidi." |
| 01 | 8.4 | Login, then the dashboard with today's sales and profit | Ingia na uone mauzo na faida ya leo |
| 02 | 8.2 | POS: adding products to the cart | Uza kwa sekunde chache |
| 03 | 8.2 | Product search and category chips | Tafuta bidhaa kwa jina au kundi |
| 04 | 11.8 | Checkout: Taslimu, amount paid, "Mauzo Yamekamilika!", change | Taslimu, Simu, Kadi au Benki. Chenji inahesabiwa yenyewe |
| 05 | 6.6 | The receipt with Chapisha and Shiriki | Risiti tayari: tazama, chapisha au shiriki |
| 06 | 15.5 | Dashboard, stock levels, sales history (sped up) | Fuatilia mauzo, hifadhi na faida |
| 07 | 10 | Orders list and an order with proforma options | Oda na ankara ya proforma kwa wateja |
| end | 6 | Logo and call to action | Sasampa POS. Pakua sasa. |

## Left out on purpose
- **WhatsApp receipts:** the app currently shows them as "coming soon", so the video does not promise them.
- **Barcode scan:** the simulator has no camera. Record this on a real phone if you want it (see below).
- **Reports and profit analysis:** these open web pages inside the app, so they were not recorded.

## Optional: record extra scenes on a real phone
Portrait, full screen, demo data only, Do Not Disturb on. Save as `video/public/clips/<id>.mp4`
using the ids above (for example `03-scan.mp4` to replace search with a barcode scan). The edit picks them up automatically.

## Tutorial series (later), same method
1. Jisajili na uanze (register, verify email, business details)
2. Ongeza bidhaa na hifadhi
3. Fanya mauzo na toa risiti
4. Oda na ankara ya proforma
5. Wateja na deni
6. Matumizi na ripoti
