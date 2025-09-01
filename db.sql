-- 0) ใช้งานสคีมา
CREATE DATABASE IF NOT EXISTS sports
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sports;

-- 1) ตารางสรุปจำนวนผู้ใช้งาน (รายชั่วโมง)
--    บังคับไม่ซ้ำด้วย (usage_date, usage_time, facility, outdoor_area)
CREATE TABLE IF NOT EXISTS usage_counts (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usage_date    DATE            NOT NULL,  -- วันที่
  usage_time    TIME            NOT NULL,  -- เวลา (ปัดเป็นชั่วโมง, เช่น 13:00:00)
  facility      ENUM('outdoor','badminton','pool','track') NOT NULL,
  outdoor_area  ENUM('football','futsal','basketball','volleyball','fitness','other') NULL,
  user_count    INT UNSIGNED    NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slot (usage_date, usage_time, facility, outdoor_area),
  KEY idx_date_facility (usage_date, facility),
  KEY idx_date_time (usage_date, usage_time),
  KEY idx_outdoor_detail (facility, outdoor_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) (ทางเลือก) ตารางติดป้ายให้เช็คอินกลางแจ้งว่าเป็นสนามย่อยใด
--    ไม่จำเป็นต้องกรอกครบทุกแถว ใส่เฉพาะที่รู้จักสนามย่อย
CREATE TABLE IF NOT EXISTS checkin_outdoor_tags (
  checkin_id   BIGINT UNSIGNED NOT NULL,
  outdoor_area ENUM('football','futsal','basketball','volleyball','fitness','other') NOT NULL,
  PRIMARY KEY (checkin_id),
  CONSTRAINT fk_tags_checkin
    FOREIGN KEY (checkin_id) REFERENCES checkins(id)
    ON DELETE CASCADE
);

-- 3) ตัวช่วย: มุมมองรวม checkins + ป้ายสนามย่อย (ถ้ามี)
DROP VIEW IF EXISTS v_checkins_with_outdoor;
CREATE VIEW v_checkins_with_outdoor AS
SELECT
  c.id,
  c.ts,
  DATE(c.ts) AS session_date,
  TIME(c.ts) AS session_time,
  c.facility,
  t.outdoor_area
FROM checkins c
LEFT JOIN checkin_outdoor_tags t
  ON t.checkin_id = c.id;

-- 4) สร้าง PROCEDURE สำหรับ rollup รายชั่วโมง
--    จะ “แทรกหรืออัปเดต” usage_counts ตามข้อมูลในช่วงเวลาที่กำหนด
DELIMITER //
DROP PROCEDURE IF EXISTS rollup_usage_counts//
CREATE PROCEDURE rollup_usage_counts(IN p_start DATETIME, IN p_end DATETIME)
BEGIN
  /*
    สรุปจำนวนรายชั่วโมง โดยปัดเวลาเป็นชั่วโมงด้วย HOUR(ts)
    - โซนทั่วไป (badminton/pool/track): outdoor_area เป็น NULL
    - โซน outdoor: ใช้ค่าจาก tags ถ้ามี มิฉะนั้นนับเป็น 'other'
    หมายเหตุ: ใช้ INSERT ... ON DUPLICATE KEY UPDATE เพื่อกันซ้ำ
  */
  INSERT INTO usage_counts (usage_date, usage_time, facility, outdoor_area, user_count)
  SELECT
    DATE(ts) AS usage_date,
    MAKETIME(HOUR(ts), 0, 0) AS usage_time,
    facility,
    CASE
      WHEN facility = 'outdoor' THEN COALESCE(outdoor_area, 'other')
      ELSE NULL
    END AS outdoor_area,
    COUNT(*) AS user_count
  FROM v_checkins_with_outdoor
  WHERE ts >= p_start AND ts < p_end
  GROUP BY DATE(ts), HOUR(ts), facility,
           CASE WHEN facility = 'outdoor' THEN COALESCE(outdoor_area, 'other') ELSE NULL END
  ON DUPLICATE KEY UPDATE
    user_count = VALUES(user_count);
END//
DELIMITER ;

-- 5) รันสรุปย้อนหลังทั้งหมดหนึ่งครั้ง (จากข้อมูลแรกใน checkins จนถึงปัจจุบัน)
--    ถ้ายังไม่มีข้อมูล ก็จะไม่ทำอะไร
SET @min_ts = (SELECT MIN(ts) FROM checkins);
SET @max_ts = NOW();
-- ป้องกันกรณีไม่มีข้อมูล
SET @min_ts = IFNULL(@min_ts, NOW());
CALL rollup_usage_counts(@min_ts, @max_ts);

-- 6) สร้าง EVENT ให้รันอัตโนมัติทุก 5 นาที (อัปเดตชั่วโมงปัจจุบัน)
--    ต้องเปิด event_scheduler ก่อน: SET GLOBAL event_scheduler = ON;
DROP EVENT IF EXISTS ev_rollup_usage_counts;
CREATE EVENT ev_rollup_usage_counts
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
  /*
    สรุปเฉพาะข้อมูล "วันปัจจุบัน" ตั้งแต่ 00:00 น. ถึงเวลาปัจจุบัน
    เพื่อให้ชั่วโมงที่กำลังไหลอยู่ถูกอัปเดตเรื่อย ๆ
  */
  CALL rollup_usage_counts(CONCAT(CURDATE(), ' 00:00:00'), NOW());
END;
CREATE TABLE checkins (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email        VARCHAR(255) NOT NULL,
  facility_id  VARCHAR(50)  NOT NULL,   -- pool/track/badminton/outdoor/...
  type         ENUM('checkin','checkout') NOT NULL DEFAULT 'checkin',
  session_date DATE NOT NULL,
  created_at   DATETIME NOT NULL,
  KEY idx_session_facility (session_date, facility_id),
  KEY idx_email_created (email, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;