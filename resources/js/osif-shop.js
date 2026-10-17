// The OSIF companies' pages: a product filter, and a cart per company that is
// sent to the company as a ready-written WhatsApp message. The cart lives in
// the visitor's browser (localStorage), so doctors need no account.

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

const money = (amount) => `${new Intl.NumberFormat('en-US').format(amount)} د.ع`;

/** localStorage can be blocked (private mode, settings); the cart then lasts for the visit only. */
const storage = {
    read(key, fallback) {
        try {
            const value = window.localStorage.getItem(key);
            return value === null ? fallback : JSON.parse(value);
        } catch {
            return fallback;
        }
    },
    write(key, value) {
        try {
            window.localStorage.setItem(key, JSON.stringify(value));
        } catch {
            // Keep working without persistence.
        }
    },
};

/** The list of companies: a simple name search. */
Alpine.data('osifCompanies', () => ({
    query: '',

    matches(name) {
        const query = this.query.trim().toLowerCase();
        return query === '' || name.toLowerCase().includes(query);
    },
}));

/** One company's page and cart. */
Alpine.data('osifShop', ({ company, event, products }) => ({
    company,
    event,
    products: new Map(products.map((product) => [product.id, product])),
    cartKey: `osif-cart-${company.id}`,
    items: {}, // product id → quantity
    filter: 'all', // all | offers
    query: '',
    cartOpen: false,
    detail: null, // the product id shown in the details dialog
    sent: false,
    buyer: '',
    note: '',
    toast: null,
    toastTimer: null,

    init() {
        // Drop anything the company has since removed or hidden.
        const saved = storage.read(this.cartKey, {});
        this.items = Object.fromEntries(Object.entries(saved)
            .map(([id, qty]) => [Number(id), Math.max(0, Math.min(999, Number(qty) || 0))])
            .filter(([id, qty]) => qty > 0 && this.products.has(id)));
        this.buyer = storage.read('osif-buyer-name', '');

        this.$watch('items', (items) => storage.write(this.cartKey, items));
        this.$watch('buyer', (buyer) => storage.write('osif-buyer-name', buyer));
    },

    // Catalogue

    visible(id, name) {
        const product = this.products.get(id);
        const query = this.query.trim().toLowerCase();

        return (this.filter === 'all' || product?.offer)
            && (query === '' || name.toLowerCase().includes(query));
    },

    get visibleCount() {
        const query = this.query.trim().toLowerCase();

        return [...this.products.values()]
            .filter((product) => (this.filter === 'all' || product.offer)
                && (query === '' || product.name.toLowerCase().includes(query)))
            .length;
    },

    // Cart

    qty(id) {
        return this.items[id] ?? 0;
    },

    add(id) {
        this.setQty(id, this.qty(id) + 1);
        this.sent = false;

        if (this.qty(id) === 1) {
            this.flash(`أُضيف «${this.products.get(id)?.name}» إلى السلة`);
        }
    },

    remove(id) {
        this.setQty(id, this.qty(id) - 1);
    },

    setQty(id, qty) {
        const items = { ...this.items };
        const value = Math.max(0, Math.min(999, Number(qty) || 0));

        if (value === 0) {
            delete items[id];
        } else {
            items[id] = value;
        }

        this.items = items;
    },

    get lines() {
        return Object.entries(this.items).map(([id, qty]) => {
            const product = this.products.get(Number(id));

            return {
                ...product,
                qty,
                total: product.price === null ? null : product.price * qty,
            };
        });
    },

    get count() {
        return Object.values(this.items).reduce((sum, qty) => sum + qty, 0);
    },

    get total() {
        return this.lines.reduce((sum, line) => sum + (line.total ?? 0), 0);
    },

    get hasUnpriced() {
        return this.lines.some((line) => line.price === null);
    },

    money,

    priceLabel(price) {
        return price === null ? 'السعر عند الطلب' : money(price);
    },

    clear() {
        this.items = {};
        this.note = '';
        this.sent = false;
    },

    // Checkout

    get message() {
        const text = [
            `مرحباً ${this.company.name}،`,
            `أودّ طلب المنتجات التالية من صفحتكم في ${this.event}:`,
            '',
        ];

        this.lines.forEach((line, index) => {
            text.push(`${index + 1}. ${line.name}`);
            text.push(line.price === null
                ? `   الكمية: ${line.qty} — السعر عند الطلب`
                : `   الكمية: ${line.qty} × ${money(line.price)} = ${money(line.total)}`);

            if (line.offerNote) {
                text.push(`   العرض: ${line.offerNote}`);
            }
        });

        text.push('');

        if (this.total > 0) {
            text.push(`المجموع: ${money(this.total)}${this.hasUnpriced ? ' (عدا الأصناف ذات السعر عند الطلب)' : ''}`);
        }

        if (this.buyer.trim()) {
            text.push(`الاسم: ${this.buyer.trim()}`);
        }

        if (this.note.trim()) {
            text.push(`ملاحظات: ${this.note.trim()}`);
        }

        return text.join('\n');
    },

    get whatsappUrl() {
        return `https://wa.me/${this.company.whatsapp}?text=${encodeURIComponent(this.message)}`;
    },

    // Dialogs

    openCart() {
        this.detail = null;
        this.cartOpen = true;
    },

    show(id) {
        this.detail = id;
    },

    get detailProduct() {
        return this.detail === null ? null : this.products.get(this.detail);
    },

    flash(message) {
        this.toast = message;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => {
            this.toast = null;
        }, 2200);
    },
}));

window.Alpine = Alpine;
Alpine.start();
