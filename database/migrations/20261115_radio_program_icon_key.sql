ALTER TABLE radio_programs
  ADD COLUMN IF NOT EXISTS icon_key VARCHAR(40) NOT NULL DEFAULT 'leaf' AFTER cover_image_path;

UPDATE radio_programs
SET icon_key = CASE slug
  WHEN 'premio' THEN 'sun'
  WHEN 'sembrando-vida' THEN 'leaf'
  WHEN 'manos-que-ayudan' THEN 'hands'
  WHEN 'generacion-con-futuro' THEN 'book'
  WHEN 'embajadores-de-la-solidaridad' THEN 'heart'
  WHEN 'vida-animal' THEN 'paw'
  ELSE icon_key
END;
