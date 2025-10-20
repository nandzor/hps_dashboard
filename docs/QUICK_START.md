# ⚡ Quick Start Guide

**Get CCTV Dashboard running in 5 minutes!**

---

## 🚀 ONE-LINE SETUP

```bash
./setup.sh
```

That's it! The script will:
- ✅ Check requirements
- ✅ Install dependencies
- ✅ Setup database
- ✅ Seed test data
- ✅ Build assets
- ✅ Create storage directories

---

## 🏁 START APPLICATION

```bash
# Terminal 1: Start server
php artisan serve

# Terminal 2: Start queue worker
php artisan queue:work
```

**Access:** http://localhost:8000

---

## 🔐 DEFAULT CREDENTIALS

**Admin:**
```
Email: admin@cctv.com
Password: admin123
```

**Operator:**
```
Email: operator.jakarta@cctv.com
Password: password
```

**API Testing:**
```
Key: cctv_test_dev_key
Secret: secret_test_dev_2024
```

---

## 📡 TEST API

```bash
# Run automated tests
./test_detection_api.sh

# Or test manually
curl -X GET "http://localhost:8000/api/detection/summary" \
  -H "X-API-Key: cctv_test_dev_key" \
  -H "X-API-Secret: secret_test_dev_2024"
```

**Or import Postman:**
- File: `postman_collection.json`
- Import into Postman and run collection

---

## 📂 KEY PAGES

| Page | URL | Description |
|------|-----|-------------|
| Dashboard | `/dashboard` | Overview & statistics |
| Branches | `/company-branches` | Branch management |
| Devices | `/device-masters` | Device management |
| Person Tracking | `/re-id-masters` | Re-ID tracking |
| Layouts | `/cctv-layouts` | CCTV layouts (Admin) |
| Event Logs | `/event-logs` | Event monitoring |
| Reports | `/reports/dashboard` | Analytics & reports |

---

## 🛠️ COMMON COMMANDS

```bash
# Reset database
php artisan migrate:fresh --seed

# Clear caches
php artisan optimize:clear

# Check routes
php artisan route:list

# Check queue
php artisan queue:monitor

# Run tests
./test_detection_api.sh
```

---

## 📚 DOCUMENTATION

| File | Purpose |
|------|---------|
| `README.md` | Project overview |
| `SETUP_GUIDE.md` | Detailed installation |
| `API_DETECTION_DOCUMENTATION.md` | API reference |
| `TESTING_GUIDE.md` | Testing procedures |
| `DEPLOYMENT_CHECKLIST.md` | Deployment guide |

---

## 🐛 TROUBLESHOOTING

**Port already in use?**
```bash
php artisan serve --port=8001
```

**Database connection error?**
```bash
# Check .env file
# Verify database exists: createdb cctv_dashboard
```

**Queue not processing?**
```bash
# Restart queue worker
php artisan queue:restart
php artisan queue:work
```

**Assets not loading?**
```bash
npm run build
php artisan storage:link
```

---

## ✅ VERIFICATION CHECKLIST

After setup, verify:
- [ ] Can access http://localhost:8000
- [ ] Can login as admin
- [ ] Dashboard displays statistics
- [ ] Can create/edit branches
- [ ] API returns data
- [ ] Queue worker running

---

## 🎯 NEXT STEPS

1. **Explore the application** - Try all CRUD operations
2. **Test the API** - Run `./test_detection_api.sh`
3. **Read documentation** - Check `API_DETECTION_DOCUMENTATION.md`
4. **Deploy to staging** - Follow `DEPLOYMENT_CHECKLIST.md`

---

**That's it! You're ready to go! 🎉**

For detailed docs, see: `README.md` or `SETUP_GUIDE.md`

