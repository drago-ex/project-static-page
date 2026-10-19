--
--  Static page: description for search engines
-- --------------------------------------------
ALTER TABLE static_page
  ADD description VARCHAR(255) NULL AFTER title;
