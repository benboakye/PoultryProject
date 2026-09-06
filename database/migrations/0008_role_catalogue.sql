INSERT INTO roles (slug, name, scope_level) VALUES
    ('farmer', 'Farmer', 'self'),
    ('farm_worker', 'Farm worker', 'farm'),
    ('extension_officer', 'Extension officer', 'district'),
    ('vet_officer', 'Veterinary officer', 'district'),
    ('dealer', 'Accredited dealer', 'outlet'),
    ('processor', 'Processor', 'facility'),
    ('district_admin', 'District administrator', 'district'),
    ('regional_admin', 'Regional administrator', 'region'),
    ('national_admin', 'National administrator', 'national'),
    ('auditor', 'Auditor', 'national');
