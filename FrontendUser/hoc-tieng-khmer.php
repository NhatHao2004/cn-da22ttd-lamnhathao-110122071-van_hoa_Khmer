<?php
/**
 * Học tiếng Khmer - Bảng chữ cái Khmer
 * Unified Design - Timeline Style
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = 'Học Bảng Chữ Cái Khmer';

// Bảng chữ cái Khmer - Phụ âm (Consonants)
$khmerConsonants = [
    ['letter' => 'ក', 'name' => 'Ka', 'sound' => 'kò', 'example' => 'កា (ka) - crow'],
    ['letter' => 'ខ', 'name' => 'Kha', 'sound' => 'kho', 'example' => 'ខ្យល់ (khyal) - wind'],
    ['letter' => 'គ', 'name' => 'Ko', 'sound' => 'khồ', 'example' => 'គោ (ko) - cow'],
    ['letter' => 'ឃ', 'name' => 'Kho', 'sound' => 'khố', 'example' => 'ឃ្លាំង (khleang) - warehouse'],
    ['letter' => 'ង', 'name' => 'Ngo', 'sound' => 'ngồ', 'example' => 'ងូ (ngu) - stupid'],
    ['letter' => 'ច', 'name' => 'Cha', 'sound' => 'ch', 'example' => 'ចាន (chan) - plate'],
    ['letter' => 'ឆ', 'name' => 'Chha', 'sound' => 'chh', 'example' => 'ឆា (chha) - to fry'],
    ['letter' => 'ជ', 'name' => 'Cho', 'sound' => 'ch', 'example' => 'ជើង (cheung) - leg'],
    ['letter' => 'ឈ', 'name' => 'Chho', 'sound' => 'chh', 'example' => 'ឈើ (chheu) - wood'],
    ['letter' => 'ញ', 'name' => 'Nyo', 'sound' => 'ny', 'example' => 'ញាំ (nyam) - to eat'],
    ['letter' => 'ដ', 'name' => 'Da', 'sound' => 'd', 'example' => 'ដៃ (dai) - hand'],
    ['letter' => 'ឋ', 'name' => 'Tha', 'sound' => 'th', 'example' => 'ឋាន (than) - place'],
    ['letter' => 'ឌ', 'name' => 'Do', 'sound' => 'd', 'example' => 'ឌុប (dop) - to dip'],
    ['letter' => 'ឍ', 'name' => 'Tho', 'sound' => 'th', 'example' => 'ឍាន (than) - large'],
    ['letter' => 'ណ', 'name' => 'Na', 'sound' => 'n', 'example' => 'ណា (na) - which'],
    ['letter' => 'ត', 'name' => 'Ta', 'sound' => 't', 'example' => 'តា (ta) - grandfather'],
    ['letter' => 'ថ', 'name' => 'Tha', 'sound' => 'th', 'example' => 'ថា (tha) - to say'],
    ['letter' => 'ទ', 'name' => 'To', 'sound' => 't', 'example' => 'ទឹក (teuk) - water'],
    ['letter' => 'ធ', 'name' => 'Tho', 'sound' => 'th', 'example' => 'ធំ (thom) - big'],
    ['letter' => 'ន', 'name' => 'No', 'sound' => 'n', 'example' => 'នំ (nom) - cake'],
    ['letter' => 'ប', 'name' => 'Ba', 'sound' => 'b', 'example' => 'បី (bei) - three'],
    ['letter' => 'ផ', 'name' => 'Pha', 'sound' => 'ph', 'example' => 'ផ្កា (phka) - flower'],
    ['letter' => 'ព', 'name' => 'Po', 'sound' => 'p', 'example' => 'ពណ៌ (pon) - color'],
    ['letter' => 'ភ', 'name' => 'Pho', 'sound' => 'ph', 'example' => 'ភ្នែក (phnek) - eye'],
    ['letter' => 'ម', 'name' => 'Mo', 'sound' => 'm', 'example' => 'មាន (mean) - to have'],
    ['letter' => 'យ', 'name' => 'Yo', 'sound' => 'y', 'example' => 'យក (yok) - to take'],
    ['letter' => 'រ', 'name' => 'Ro', 'sound' => 'r', 'example' => 'រស់ (ros) - to live'],
    ['letter' => 'ល', 'name' => 'Lo', 'sound' => 'l', 'example' => 'លើ (leu) - on/above'],
    ['letter' => 'វ', 'name' => 'Vo', 'sound' => 'v', 'example' => 'វា (vea) - it'],
    ['letter' => 'ស', 'name' => 'Sa', 'sound' => 's', 'example' => 'សេះ (seh) - horse'],
    ['letter' => 'ហ', 'name' => 'Ha', 'sound' => 'h', 'example' => 'ហា (ha) - to open mouth'],
    ['letter' => 'ឡ', 'name' => 'La', 'sound' => 'l', 'example' => 'ឡាន (lan) - car'],
    ['letter' => 'អ', 'name' => 'A', 'sound' => 'a', 'example' => 'អី (ei) - what']
];

// Bảng nguyên âm Khmer (Vowels)
$khmerVowels = [
    ['letter' => 'ា', 'name' => 'aa', 'sound' => 'aa', 'example' => 'កា (kaa)'],
    ['letter' => 'ិ', 'name' => 'e', 'sound' => 'i', 'example' => 'កិ (ke)'],
    ['letter' => 'ី', 'name' => 'ei', 'sound' => 'ii', 'example' => 'កី (kei)'],
    ['letter' => 'ឹ', 'name' => 'oe', 'sound' => 'ue', 'example' => 'កឹ (koe)'],
    ['letter' => 'ឺ', 'name' => 'oeu', 'sound' => 'uee', 'example' => 'កឺ (koeu)'],
    ['letter' => 'ុ', 'name' => 'o', 'sound' => 'u', 'example' => 'កុ (ko)'],
    ['letter' => 'ូ', 'name' => 'ou', 'sound' => 'uu', 'example' => 'កូ (kou)'],
    ['letter' => 'ួ', 'name' => 'uor', 'sound' => 'uor', 'example' => 'កួ (kuor)'],
    ['letter' => 'ើ', 'name' => 'aeu', 'sound' => 'eu', 'example' => 'កើ (kaeu)'],
    ['letter' => 'ឿ', 'name' => 'oea', 'sound' => 'uea', 'example' => 'កឿ (koea)'],
    ['letter' => 'ៀ', 'name' => 'ie', 'sound' => 'ie', 'example' => 'កៀ (kie)'],
    ['letter' => 'េ', 'name' => 'e', 'sound' => 'e', 'example' => 'កេ (ke)'],
    ['letter' => 'ែ', 'name' => 'ae', 'sound' => 'ae', 'example' => 'កែ (kae)'],
    ['letter' => 'ៃ', 'name' => 'ai', 'sound' => 'ai', 'example' => 'កៃ (kai)'],
    ['letter' => 'ោ', 'name' => 'o', 'sound' => 'o', 'example' => 'កោ (ko)'],
    ['letter' => 'ៅ', 'name' => 'au', 'sound' => 'au', 'example' => 'កៅ (kau)']
];

// Số đếm Khmer (Numbers)
$khmerNumbers = [
    ['letter' => '០', 'name' => 'Zero', 'value' => '0'],
    ['letter' => '១', 'name' => 'Muoy', 'value' => '1'],
    ['letter' => '២', 'name' => 'Pir', 'value' => '2'],
    ['letter' => '៣', 'name' => 'Bei', 'value' => '3'],
    ['letter' => '៤', 'name' => 'Buon', 'value' => '4'],
    ['letter' => '៥', 'name' => 'Pram', 'value' => '5'],
    ['letter' => '៦', 'name' => 'Pram Muoy', 'value' => '6'],
    ['letter' => '៧', 'name' => 'Pram Pir', 'value' => '7'],
    ['letter' => '៨', 'name' => 'Pram Bei', 'value' => '8'],
    ['letter' => '៩', 'name' => 'Pram Buon', 'value' => '9']
];

// Filter
$activeTab = sanitize($_GET['tab'] ?? 'consonants');
$search = sanitize($_GET['search'] ?? '');

// Filter data based on search
function filterItems($items, $search, $type = 'letter') {
    if (empty($search)) return $items;
    return array_filter($items, function($item) use ($search, $type) {
        $searchLower = mb_strtolower($search);
        return mb_strpos(mb_strtolower($item['letter']), $searchLower) !== false ||
               mb_strpos(mb_strtolower($item['name']), $searchLower) !== false ||
               (isset($item['sound']) && mb_strpos(mb_strtolower($item['sound']), $searchLower) !== false) ||
               (isset($item['example']) && mb_strpos(mb_strtolower($item['example']), $searchLower) !== false);
    });
}

$filteredConsonants = filterItems($khmerConsonants, $search);
$filteredVowels = filterItems($khmerVowels, $search);
$filteredNumbers = filterItems($khmerNumbers, $search);
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Khmer Learning Hero Section ===== */
.khmer-hero {
    min-height: 40vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFCC80 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
}

.khmer-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.khmer-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.2);
}

.khmer-hero-subtitle {
    font-size: 1.125rem;
    color: #2d2d2d;
    font-weight: 600;
    max-width: 600px;
    margin: 0 auto 1rem;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    flex-wrap: wrap;
}

.hero-stat { 
    text-align: center;
    padding: 1.5rem 2rem;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 16px;
    border: 3px solid #1a1a1a;
    box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
}

.hero-stat-number { 
    font-size: 2rem; 
    font-weight: 900; 
    display: block;
    color: #FF9800;
}

.hero-stat-label { 
    font-size: 0.875rem;
    color: #1a1a1a;
    font-weight: 700;
}
</style>

<style>
/* ===== Main Content Area ===== */
.khmer-main {
    padding: 2rem 0;
    background: linear-gradient(180deg, #FFF6E5 0%, #FFE0B2 100%);
    min-height: 60vh;
}

/* ===== Filter Bar ===== */
.filter-section {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.25);
    border: 3px solid #1a1a1a;
}

.filter-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-search {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.filter-search input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 2px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #1a1a1a;
}

.filter-search input:focus {
    outline: none;
    border-color: #FF9800;
    background: white;
    box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.2);
}

.filter-search i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #FF9800;
    font-weight: 600;
}

.filter-tabs {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 0.875rem 1.5rem;
    background: #ffffff;
    border: 2px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    color: #1a1a1a;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-tab:hover {
    background: #FF9800;
    color: #ffffff;
    border-color: #FF9800;
}

.filter-tab.active {
    background: #FF9800;
    color: #ffffff;
    border-color: #FF9800;
}

.filter-btn {
    padding: 0.875rem 1.5rem;
    background: #ffffff;
    color: #1a1a1a;
    border: 2px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.filter-btn:hover {
    background: #FF9800;
    color: #ffffff;
    border-color: #FF9800;
    transform: translateY(-2px);
}

.filter-reset {
    padding: 0.875rem 1.25rem;
    background: #ffffff;
    color: #1a1a1a;
    border: 2px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.filter-reset:hover {
    background: #FF9800;
    color: #ffffff;
    border-color: #FF9800;
}

/* Active Filters */
.active-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #FFE0B2;
    color: #1a1a1a;
    border-radius: 50px;
    font-size: 0.8125rem;
    font-weight: 600;
    border: 2px solid #FF9800;
}

.filter-tag a { 
    color: inherit; 
    margin-left: 0.25rem;
}
</style>

<style>
/* ===== Letter Cards Grid - Modern 3-Column Layout ===== */
.letter-grid-section {
    margin-bottom: 3rem;
}

.section-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-title i {
    color: #FF9800;
}

.results-count {
    font-size: 0.9375rem;
    color: #1a1a1a;
    font-weight: 700;
}

.results-count strong {
    color: #FF9800;
    font-weight: 900;
}

/* Letter Cards Grid - 5 Columns */
.letter-cards-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.5rem;
}

/* Letter Card - Modern Vertical Card */
.letter-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 3px solid #1a1a1a;
    cursor: pointer;
    position: relative;
    display: flex;
    flex-direction: column;
}

.letter-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(255, 152, 0, 0.4);
    border-color: #FF9800;
}

/* Letter Display Section - Top */
.letter-card-display {
    position: relative;
    width: 100%;
    height: 180px;
    overflow: hidden;
    background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.letter-card-display::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.letter-card-display::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -20%;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.letter-card-display.vowel-bg {
    background: linear-gradient(135deg, #FFB74D 0%, #FFA726 100%);
}

.letter-card-display.number-bg {
    background: linear-gradient(135deg, #FFCC80 0%, #FFB74D 100%);
}

.khmer-letter {
    font-size: 5.5rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1;
    font-family: 'Khmer OS Siemreap', 'Khmer OS', 'Noto Sans Khmer', sans-serif;
    text-shadow: 0 4px 30px rgba(0,0,0,0.3);
    position: relative;
    z-index: 1;
    transition: transform 0.4s ease;
}

.letter-card:hover .khmer-letter {
    transform: scale(1.1);
}

/* Content Section - Bottom */
.letter-card-content {
    flex: 1;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    background: white;
    position: relative;
}

.letter-card-header {
    flex: 1;
    text-align: center;
}

.letter-card-meta-row {
    display: none;
}

.letter-card-name {
    color: #1a1a1a;
    font-size: 1.5rem;
    font-weight: 900;
    margin-bottom: 0.5rem;
    line-height: 1.3;
    letter-spacing: -0.01em;
}

.letter-card-sound {
    color: #1a1a1a;
    font-size: 0.9375rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #FFE0B2;
    border-radius: 20px;
    border: 1px solid #FF9800;
}

.letter-card-example {
    color: #2d2d2d;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.5;
    margin-top: 0.5rem;
}

.letter-card-value {
    color: #FF9800;
    font-size: 1.5rem;
    font-weight: 900;
    margin-top: 0.25rem;
}

.letter-card-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    padding-top: 1rem;
    margin-top: auto;
}

.letter-card-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #ffffff;
    color: #1a1a1a;
    border: 2px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 700;
    transition: all 0.3s ease;
    cursor: pointer;
    width: 100%;
}

.letter-card:hover .letter-card-action,
.letter-card-action:hover {
    background: #FF9800;
    color: #ffffff;
    border-color: #FF9800;
}

.letter-card-action i {
    transition: transform 0.3s ease;
}

.letter-card:hover .letter-card-action i {
    transform: scale(1.15);
}

/* Card Index Badge */
.letter-card::before {
    content: attr(data-index);
    position: absolute;
    top: 1rem;
    left: 1rem;
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,0.25);
    backdrop-filter: blur(10px);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
    z-index: 2;
}
</style>

<style>
/* ===== Practice Section ===== */
.practice-section {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 3rem;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.3);
    border: 3px solid #1a1a1a;
    margin-top: 3rem;
    text-align: center;
}

.practice-title {
    font-size: 1.75rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 1rem;
}

.practice-desc {
    font-size: 1rem;
    color: #2d2d2d;
    font-weight: 600;
    margin-bottom: 2rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    white-space: nowrap;
}

.practice-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.practice-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4);
}

/* ===== Empty State ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.25);
    border: 3px solid #1a1a1a;
}

.empty-state-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
    background: #FFE0B2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #FF9800;
}

.empty-state-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
}

.empty-state-desc {
    font-size: 1rem;
    color: #2d2d2d;
    margin-bottom: 2rem;
}

.empty-state-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
}

.empty-state-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4);
}

/* ===== Responsive ===== */
@media (max-width: 1200px) {
    .letter-cards-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }
    
    .letter-card-display {
        height: 160px;
    }
    
    .khmer-letter {
        font-size: 4.5rem;
    }
    
    .letter-card-name {
        font-size: 1.375rem;
    }
}

@media (max-width: 1024px) {
    .letter-cards-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }
    
    .letter-card-display {
        height: 150px;
    }
    
    .khmer-letter {
        font-size: 4rem;
    }
    
    .letter-card-content {
        padding: 1.25rem;
    }
    
    .letter-card-name {
        font-size: 1.25rem;
    }
}

@media (max-width: 768px) {
    .letter-cards-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .letter-card-display {
        height: 140px;
    }
    
    .khmer-letter {
        font-size: 3.5rem;
    }
    
    .letter-card-content {
        padding: 1rem;
    }
    
    .letter-card-name {
        font-size: 1.125rem;
    }
    
    .letter-card-sound {
        font-size: 0.8125rem;
        padding: 0.2rem 0.5rem;
    }
    
    .letter-card-example {
        font-size: 0.8125rem;
    }
    
    .letter-card-action {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .filter-search {
        width: 100%;
    }
    
    .filter-tabs {
        width: 100%;
        justify-content: center;
    }
    
    .hero-stats {
        gap: 1.5rem;
    }
    
    .khmer-hero-title {
        font-size: 1.75rem;
    }
    
    .practice-section {
        padding: 2rem 1.5rem;
    }
    
    .practice-desc {
        white-space: normal;
        font-size: 0.9375rem;
    }
}

@media (max-width: 480px) {
    .letter-cards-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .letter-card {
        border-radius: 16px;
    }
    
    .letter-card-display {
        height: 160px;
    }
    
    .khmer-letter {
        font-size: 4rem;
    }
    
    .letter-card-content {
        padding: 1.25rem;
    }
    
    .letter-card-name {
        font-size: 1.25rem;
    }
    
    .filter-tab {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    
    .letter-card::before {
        width: 28px;
        height: 28px;
        font-size: 0.6875rem;
    }
}
</style>

<!-- Hero Section -->
<section class="khmer-hero">
    <div class="container">
        <div class="khmer-hero-content">
            <h1 class="khmer-hero-title">📚 Học Tiếng Khmer</h1>
            <p class="khmer-hero-subtitle">Khám phá hệ thống chữ viết Khmer</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="khmer-main">
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form class="filter-form" method="GET" action="">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Tìm chữ cái, phát âm..." value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">
                </div>
                
                <div class="filter-tabs">
                    <a href="?tab=consonants<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="filter-tab <?= $activeTab === 'consonants' ? 'active' : '' ?>">
                        📝 Phụ Âm (<?= count($filteredConsonants) ?>)
                    </a>
                    <a href="?tab=vowels<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="filter-tab <?= $activeTab === 'vowels' ? 'active' : '' ?>">
                        🔤 Nguyên Âm (<?= count($filteredVowels) ?>)
                    </a>
                    <a href="?tab=numbers<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="filter-tab <?= $activeTab === 'numbers' ? 'active' : '' ?>">
                        🔢 Số Đếm (<?= count($filteredNumbers) ?>)
                    </a>
                </div>
                
                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                
                <?php if ($search): ?>
                <a href="?tab=<?= $activeTab ?>" class="filter-reset">
                    <i class="fas fa-times"></i> Đặt lại
                </a>
                <?php endif; ?>
            </form>
            
            <?php if ($search): ?>
            <div class="active-filters">
                <span class="filter-tag">
                    <i class="fas fa-search"></i> "<?= htmlspecialchars($search) ?>"
                    <a href="?tab=<?= $activeTab ?>"><i class="fas fa-times"></i></a>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Consonants Tab -->
        <?php if ($activeTab === 'consonants'): ?>
        <div class="letter-grid-section">
            <div class="section-header-row">
                <h2 class="section-title">
                    Phụ Âm Khmer (អក្សរ)
                </h2>
                <span class="results-count">
                    Hiển thị <strong><?= count($filteredConsonants) ?></strong> / <strong><?= count($khmerConsonants) ?></strong> chữ cái
                </span>
            </div>
            
            <?php if (!empty($filteredConsonants)): ?>
            <div class="letter-cards-grid">
                <?php $index = 1; foreach ($filteredConsonants as $consonant): ?>
                <div class="letter-card" data-index="<?= $index ?>" onclick="speakLetter('<?= $consonant['name'] ?>')">
                    <div class="letter-card-display">
                        <div class="khmer-letter"><?= $consonant['letter'] ?></div>
                    </div>
                    <div class="letter-card-content">
                        <div class="letter-card-header">
                            <h3 class="letter-card-name"><?= $consonant['name'] ?></h3>
                            <span class="letter-card-sound">[<?= $consonant['sound'] ?>]</span>
                            <p class="letter-card-example"><?= $consonant['example'] ?></p>
                        </div>
                        <div class="letter-card-footer">
                            <button class="letter-card-action" onclick="event.stopPropagation(); speakLetter('<?= $consonant['name'] ?>')">
                                <i class="fas fa-volume-up"></i> Phát âm
                            </button>
                        </div>
                    </div>
                </div>
                <?php $index++; endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-search"></i></div>
                <h3 class="empty-state-title">Không tìm thấy kết quả</h3>
                <p class="empty-state-desc">Không có phụ âm nào phù hợp với từ khóa "<?= htmlspecialchars($search) ?>"</p>
                <a href="?tab=consonants" class="empty-state-btn"><i class="fas fa-redo"></i> Xem tất cả</a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Vowels Tab -->
        <?php if ($activeTab === 'vowels'): ?>
        <div class="letter-grid-section">
            <div class="section-header-row">
                <h2 class="section-title">
                    Nguyên Âm Khmer (ស្រៈ)
                </h2>
                <span class="results-count">
                    Hiển thị <strong><?= count($filteredVowels) ?></strong> / <strong><?= count($khmerVowels) ?></strong> ký hiệu
                </span>
            </div>
            
            <?php if (!empty($filteredVowels)): ?>
            <div class="letter-cards-grid">
                <?php $index = 1; foreach ($filteredVowels as $vowel): ?>
                <div class="letter-card" data-index="<?= $index ?>" onclick="speakLetter('<?= $vowel['name'] ?>')">
                    <div class="letter-card-display vowel-bg">
                        <div class="khmer-letter">ក<?= $vowel['letter'] ?></div>
                    </div>
                    <div class="letter-card-content">
                        <div class="letter-card-header">
                            <h3 class="letter-card-name"><?= $vowel['name'] ?></h3>
                            <span class="letter-card-sound">[<?= $vowel['sound'] ?>]</span>
                            <p class="letter-card-example"><?= $vowel['example'] ?></p>
                        </div>
                        <div class="letter-card-footer">
                            <button class="letter-card-action" onclick="event.stopPropagation(); speakLetter('<?= $vowel['name'] ?>')">
                                <i class="fas fa-volume-up"></i> Phát âm
                            </button>
                        </div>
                    </div>
                </div>
                <?php $index++; endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-search"></i></div>
                <h3 class="empty-state-title">Không tìm thấy kết quả</h3>
                <p class="empty-state-desc">Không có nguyên âm nào phù hợp với từ khóa "<?= htmlspecialchars($search) ?>"</p>
                <a href="?tab=vowels" class="empty-state-btn"><i class="fas fa-redo"></i> Xem tất cả</a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Numbers Tab -->
        <?php if ($activeTab === 'numbers'): ?>
        <div class="letter-grid-section">
            <div class="section-header-row">
                <h2 class="section-title">
                    Số Đếm Khmer (លេខ)
                </h2>
                <span class="results-count">
                    Hiển thị <strong><?= count($filteredNumbers) ?></strong> / <strong><?= count($khmerNumbers) ?></strong> số
                </span>
            </div>
            
            <?php if (!empty($filteredNumbers)): ?>
            <div class="letter-cards-grid">
                <?php $index = 1; foreach ($filteredNumbers as $number): ?>
                <div class="letter-card" data-index="<?= $index ?>" onclick="speakLetter('<?= $number['name'] ?>')">
                    <div class="letter-card-display number-bg">
                        <div class="khmer-letter"><?= $number['letter'] ?></div>
                    </div>
                    <div class="letter-card-content">
                        <div class="letter-card-header">
                            <h3 class="letter-card-name"><?= $number['name'] ?></h3>
                            <p class="letter-card-value">= <?= $number['value'] ?></p>
                        </div>
                        <div class="letter-card-footer">
                            <button class="letter-card-action" onclick="event.stopPropagation(); speakLetter('<?= $number['name'] ?>')">
                                <i class="fas fa-volume-up"></i> Phát âm
                            </button>
                        </div>
                    </div>
                </div>
                <?php $index++; endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-search"></i></div>
                <h3 class="empty-state-title">Không tìm thấy kết quả</h3>
                <p class="empty-state-desc">Không có số nào phù hợp với từ khóa "<?= htmlspecialchars($search) ?>"</p>
                <a href="?tab=numbers" class="empty-state-btn"><i class="fas fa-redo"></i> Xem tất cả</a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Practice Section -->
        <div class="practice-section">
            <h3 class="practice-title">🎯 Sẵn sàng học bài?</h3>
            <p class="practice-desc">
                Đã nắm vững bảng chữ cái? Hãy bắt đầu với các bài học tiếng Khmer từ cơ bản đến nâng cao.
            </p>
            <a href="<?= BASE_URL ?>/danh-sach-bai-hoc.php" class="practice-btn">
                <i class="fas fa-graduation-cap"></i>
                Bắt đầu học bài
            </a>
        </div>
    </div>
</section>

<script>
// Text-to-speech functionality
function speakLetter(text) {
    if ('speechSynthesis' in window) {
        // Cancel any ongoing speech
        window.speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'km-KH'; // Khmer language code
        utterance.rate = 0.8; // Slower speech rate for learning
        window.speechSynthesis.speak(utterance);
    } else {
        console.log('Text-to-speech not supported');
    }
}

// Add keyboard navigation for tabs
document.addEventListener('keydown', function(e) {
    const tabs = document.querySelectorAll('.filter-tab');
    const activeTab = document.querySelector('.filter-tab.active');
    const currentIndex = Array.from(tabs).indexOf(activeTab);
    
    if (e.key === 'ArrowRight' && currentIndex < tabs.length - 1) {
        tabs[currentIndex + 1].click();
    } else if (e.key === 'ArrowLeft' && currentIndex > 0) {
        tabs[currentIndex - 1].click();
    }
});

// Add animation on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.letter-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(card);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
