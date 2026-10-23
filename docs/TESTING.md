# SAIP — Manual Test Checklist

## Authentication
- [ ] Register new farmer → redirected to login with success message
- [ ] Login with wrong password → error shown, failed login logged
- [ ] 5 wrong attempts → account locked 15 min
- [ ] Login successful → dashboard loads, session created
- [ ] Logout → session destroyed, session marked inactive in DB
- [ ] Refresh after logout → redirected to login
- [ ] Idle 2h → auto-logout

## Authorization
- [ ] Farmer cannot access /government → redirected
- [ ] Officer CAN access /government
- [ ] Admin can access /security/auditLogs
- [ ] Farmer cannot access /security/auditLogs

## CSRF
- [ ] Submit any POST form without token → 419 error
- [ ] Submit any POST form with valid token → works

## Multilingual
- [ ] Switch to Kinyarwanda → all labels change
- [ ] Refresh → language persists
- [ ] Switch to Français, Kiswahili → all work

## Weather
- [ ] Weather page loads current temp
- [ ] 5-day forecast shows
- [ ] Map shows Rwanda districts
- [ ] Click district → popup with live weather

## Crops
- [ ] Create farm → appears in list
- [ ] Plant crop → appears in active crops
- [ ] Update growth stage → timeline updates
- [ ] Upload leaf photo → disease detected with confidence
- [ ] Ask AgriBot → intelligent reply in current language

## Marketplace
- [ ] Add product as seller
- [ ] Browse as buyer → product appears
- [ ] Order → order created
- [ ] Pay with MoMo mock → status = paid
- [ ] Leave review → seller rating updates
- [ ] Wishlist toggle works

## Chat
- [ ] Send text message → appears in chat
- [ ] Upload image → renders
- [ ] Record voice note → plays back
- [ ] Share location → Google Maps link works
- [ ] React with emoji → chip appears
- [ ] Translate message → swaps language

## Government
- [ ] National dashboard shows 6 KPIs
- [ ] Heatmap renders by production
- [ ] Click district → drill-down opens
- [ ] Publish alert → appears in alert list
- [ ] Generate PDF report → print dialog opens
- [ ] Export CSV → downloads

## Cooperative
- [ ] Create cooperative → auto-enrolled
- [ ] Add member → member appears
- [ ] Record harvest → shows in list
- [ ] Schedule meeting → all members notified

## Logistics
- [ ] Add vehicle → appears in fleet
- [ ] Request transport → fee calculated
- [ ] Update status → timeline updates

## Storage
- [ ] Create warehouse → appears
- [ ] Add bin → shows fill % + temp + moisture
- [ ] Bin expiring within 30d → shows in alerts

## Notifications
- [ ] Insert notification → bell badge shows count
- [ ] Click bell → notifications page
- [ ] Mark as read → badge decreases
- [ ] Mark all read → clears

## Performance
- [ ] Login < 2s
- [ ] Dashboard < 3s
- [ ] Map load < 5s
- [ ] Report generation < 10s