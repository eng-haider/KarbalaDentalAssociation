// Shared by the composition, capture and final-file verification.
export const FPS = 30;
export const FRAMES = 28 * FPS;
export const HERO_FRAMES = 132;
export const DISCOUNT_FRAMES = 168;
export const SECTION_TRANSITION = 18;
export const CLOSING_START = 21 * FPS;
export const CLOSING_FRAMES = FRAMES - CLOSING_START;
export const CTA_FRAME = 24 * FPS;
export const REVIEW_FRAMES = [48, 126, 192, 255, 345, 435, 525, 603, 681, 786];
export const SECTIONS = [
  { file: 'search.png', label: 'الخدمات الإلكترونية', start: 210, hold: 90 },
  { file: 'news.png', label: 'نشاطات النقابة', start: 300, hold: 90 },
  { file: 'courses.png', label: 'الدورات التدريبية', start: 390, hold: 90 },
  { file: 'discounts-page.png', label: 'خصومات أعضاء النقابة', start: 480, hold: 150, footage: true },
];
