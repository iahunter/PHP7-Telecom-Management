#!/bin/bash

# This script will install the tms scheduled jobs on a new server. 

# Make sure this file is executable. 
# chmod +rwx crontab_setup.sh

printf "\n###########################################################\n"
printf "Install Crontab Tasks Script \n"

echo "The current working directory: $PWD"
_mydir="$PWD"
echo $_mydir

now=$(date +"%T")
echo "Creating Cron Jobs : $now"
(crontab -l ; echo "#################### TMS CRON JOBS ###########################")| crontab -
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Infrastructure Monitoring - Pings all device in monitoring state")| crontab -
(crontab -l ; echo "*/1 * * * * "$_mydir"/artisan monitoring:pingscan_infrastructure >> $_mydir/storage/logs/ping-scan-infrastructure.log 2>&1")| crontab -

# Polls Sonus alarms and will alert on them. 
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# SBC Alarm Monitor")| crontab -
(crontab -l ; echo "*/1 * * * * "$_mydir"/artisan monitoring:sonus_alarm_monitor >> "$_mydir"/storage/logs/sonus_alarm_monitor.log 2>&1")| crontab -

# Monitors for large number of attemps and will alert on threasholds set. 
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# SBC CDR Attempt Monitor")| crontab -
(crontab -l ; echo "*/10 * * * * "$_mydir"/artisan monitoring:sonus_cdr_attempt_monitor >> "$_mydir"/storage/logs/sonus_cdr_attempt_monitor.log 2>&1")| crontab -

# Watches for loops in Sonus CDRs. Will actively try to remove loops in CUCM call forwarding. 
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# CDR Loop Monitor")| crontab -
(crontab -l ; echo "*/5 * * * * "$_mydir"/artisan monitoring:cucm_sonus_loop_mitigator >> "$_mydir"/storage/logs/cucm_sonus_loop_mitigator.log 2>&1")| crontab -

# Monitors for stuck jobs in MACD queue. Will restart supervisor in case of stuck jobs. 
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# MACD Monitor")| crontab -
(crontab -l ; echo "*/5 * * * * "$_mydir"/artisan monitoring:macd_job_monitor >> "$_mydir"/storage/logs/macd_job_monitor.log 2>&1")| crontab -

# Get Sonus Call counts from SBC and save to DB
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Get SBC Call stats every 5 mins")| crontab -
(crontab -l ; echo "*/5 * * * * "$_mydir"/artisan sonus:write-callsummary-db > /dev/null 2>&1")| crontab -

# Get Sonus CDR Data from SBC and store in DB. Needs adjusted for larger call volume. Created with about 300 calls peak
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Get SBC CDRs every 5 mins")| crontab -
(crontab -l ; echo "*/5 * * * * "$_mydir"/artisan sonus:write-cdrs-to-db > /dev/null 2>&1")| crontab -

# Cache Sonus Graph Data
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Cache SBC Graph Data every 5 mins for faster graph loading")| crontab -
(crontab -l ; echo "*/5 * * * * "$_mydir"/artisan sonus:save_call_reports_to_cache > /dev/null 2>&1")| crontab -

# Gat Call data from Confgured H323 Gateways
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Get Gateway Call stats every 10 mins")| crontab -
(crontab -l ; echo "*/10 * * * * "$_mydir"/artisan callmanager:cucm-gateway-call-counts > /dev/null 2>&1")| crontab -

# Used to Cache Active Calls 
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Store SBC Active Calls to Cache - this script pulls every 5 secs for 1 minute - run every minute")| crontab -
(crontab -l ; echo "*/1 * * * * "$_mydir"/store-sonus-active-calls-to-cache.sh > /dev/null 2>&1")| crontab -

# Saves Phone Names in Cache for Bulk Phone Planner / MACD Tool
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Store Phone Names in Cache for Phone Plan and MACD to use.")| crontab -
(crontab -l ; echo "*/1 * * * * "$_mydir"/artisan callmanager:phone_names_cache > /dev/null 2>&1")| crontab -

# DID Status Scan
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# CUCM - DID scan - Update Status every 10 mins")| crontab -
(crontab -l ; echo "*/10 * * * * "$_mydir"/artisan callmanager:didscan > /dev/null 2>&1")| crontab -

# Requires West 911Enable EGW DB Access
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Update Phone Database with IP And ERL Info from EGW scan every hour")| crontab -
(crontab -l ; echo "0 * * * * "$_mydir"/artisan west911enable:update_db_phonetable_ip_and_erl > /dev/null 2>&1")| crontab -

# Scans RISDB realtime data and updates the database with IPs and Registration status. 
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Update Phone Database with CUCM Risdb scan every 15 mins")| crontab -
(crontab -l ; echo "*/15 * * * * "$_mydir"/artisan callmanager:phone_ip_address_and_status_scan > /dev/null 2>&1")| crontab -

# CUCM CDR run in production only. Requires SFTP Server
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Get CUCM CDR and CMRs from SFTP Server every min")| crontab -
(crontab -l ; echo "#*/1 * * * * "$_mydir"/artisan callmanager:getcdrs >> "$_mydir"/storage/logs/cucm_cdr.log 2>&1")| crontab -

# Number Cleanup Report - Run in Production Only
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# CUCM Clean up Report - stores data for evaluation only runs at 0100")| crontab -
(crontab -l ; echo "#0 1 * * * "$_mydir"/artisan callmanager:cleanup_unused_numbers > /dev/null 2>&1")| crontab -

# Unity DID Scan 
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Unity Connection - DID Scan at 0200")| crontab -
(crontab -l ; echo "0 2 * * * "$_mydir"/artisan cisco_unity_connection:didscan > /dev/null 2>&1")| crontab -

# Use shell script in Production only
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# CUCM - Site Scan Configs every hour")| crontab -
(crontab -l ; echo "0 * * * * "$_mydir"/artisan callmanager:sitescan >> "$_mydir"/storage/logs/sitescan.log 2>&1")| crontab -
(crontab -l ; echo "#0 * * * * "$_mydir"/grab-cucm-siteconfigs.sh >> "$_mydir"/storage/logs/grab-cucm-siteconfigs.log 2>&1")| crontab -

# Use shell script in Production only
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# CUCM - Phone Scan Configs at 0100")| crontab -
(crontab -l ; echo "0 1 * * * "$_mydir"/artisan callmanager:phonescan > /dev/null 2>&1")| crontab -
(crontab -l ; echo "#0 1 * * * "$_mydir"/grab-cucm-phoneconfigs.sh > /dev/null 2>&1 ")| crontab -

#Backups
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Telecom Management Backups at 0400")| crontab -
(crontab -l ; echo "#0 4 * * * "$_mydir"/artisan backup:run > /dev/null 2>&1")| crontab -
(crontab -l ; echo "# Cleanup backups every week")| crontab -
(crontab -l ; echo "#5 8 * * 7 "$_mydir"/artisan backup:clean > /dev/null 2>&1")| crontab -

#Sonus Config Backups
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Sonus - Get Conifgs - Scheduled Jobs every hour")| crontab -
(crontab -l ; echo "#0 * * * * "$_mydir"/grab-sonus-configs.sh >> "$_mydir"/storage/logs/grab-sonus-configs.log 2>&1")| crontab -

# Cleanup Sonus CDRs on box
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Sonus - Cleanup up .ACT records older than 30 days off the SBC every 30 days")| crontab -
(crontab -l ; echo "#0 0 1 * * "$_mydir"/artisan sonus:log_cleanup > /dev/null 2>&1")| crontab -

# Cleanup Sonus CDRs from MySQL DB
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# Sonus - Cleanup old CDR Records from DB")| crontab -
(crontab -l ; echo "#0 0 * * * "$_mydir"/artisan sonus:cleanup_cdr_db >> "$_mydir"/storage/logs/sonus_db_cdr_cleanup.log 2>&1")| crontab -

# Cleanup CUCM CDRs from MySQL DB
(crontab -l ; echo "")| crontab -
(crontab -l ; echo "# CUCM - Cleanup old CDR Records from DB")| crontab -
(crontab -l ; echo "#0 22 * * * "$_mydir"/artisan callmanager:cleanup_cdr_db >> "$_mydir"/storage/logs/cucm_db_cdr_cleanup.log 2>&1")| crontab -

now=$(date +"%T")
echo "Done with Cron Jobs : $now"

