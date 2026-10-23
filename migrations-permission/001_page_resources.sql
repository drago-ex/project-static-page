--  Privileges of the static page administration for drago-ex/project-permission
-- ----------------------------------------------------------------------------
-- Imported by hand into the tables of project-permission (see the README), so roles can be given these privileges
-- in the administration. They are what the Authorization trait checks: a page needs "{action}-read",
-- a component's signals "{component}-read" (sorting, paging) or "{component}-write".
INSERT IGNORE INTO resources (resource, privilege, description) VALUES
    ('Backend:Page', 'default-read', 'View static pages'),
    ('Backend:Page', 'pages-read', 'Sort and filter static pages'),
    ('Backend:Page', 'pages-write', 'Manage static pages');
