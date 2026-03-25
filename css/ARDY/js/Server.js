/**
 * MAJESTIC REALTIES BLOG — Backend API Server
 * Stack: Node.js + Express + SQLite (better-sqlite3)
 * 
 * Endpoints:
 *   GET    /api/posts              — list posts with pagination & filter
 *   GET    /api/posts/latest       — latest 5 posts (sidebar)
 *   GET    /api/posts/:slug        — single post by slug
 *   GET    /api/categories         — all categories with post counts
 *   GET    /api/posts/:id/comments — comments for a post
 *   POST   /api/posts/:id/comments — add a comment
 *   POST   /api/posts/:id/like     — toggle like (returns new count)
 *   GET    /api/posts/:id/related  — related posts
 */

const express    = require('express');
const Database   = require('better-sqlite3');
const cors       = require('cors');
const path       = require('path');
const { marked } = require('marked');

const app = express();
const PORT = process.env.PORT || 3000;

// ── Middleware ──────────────────────────────────────────────
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, '..'))); // serve HTML/CSS/JS

// ── Database Setup ──────────────────────────────────────────
const db = new Database(path.join(__dirname, 'blog.db'));
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');

// ── Schema ──────────────────────────────────────────────────
db.exec(`
  /* POSTS TABLE */
  CREATE TABLE IF NOT EXISTS posts (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    slug        TEXT UNIQUE NOT NULL,
    title       TEXT NOT NULL,
    excerpt     TEXT NOT NULL,
    content     TEXT NOT NULL,         -- Markdown/HTML body
    cover_image TEXT,                  -- URL or relative path
    category    TEXT NOT NULL DEFAULT 'General',
    tags        TEXT DEFAULT '[]',     -- JSON array of tag strings
    author_name TEXT NOT NULL DEFAULT 'Admin',
    author_role TEXT DEFAULT 'Real Estate Expert',
    author_bio  TEXT,
    author_avatar TEXT,
    likes_count INTEGER NOT NULL DEFAULT 0,
    views_count INTEGER NOT NULL DEFAULT 0,
    published   INTEGER NOT NULL DEFAULT 1,  -- 0=draft, 1=published
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT NOT NULL DEFAULT (datetime('now'))
  );

  /* COMMENTS TABLE */
  CREATE TABLE IF NOT EXISTS comments (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id    INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    name       TEXT NOT NULL,
    email      TEXT,
    body       TEXT NOT NULL,
    approved   INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
  );

  /* LIKES TABLE — tracks unique likes by session/IP */
  CREATE TABLE IF NOT EXISTS likes (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id    INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    fingerprint TEXT NOT NULL,          -- hashed IP + user-agent
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE(post_id, fingerprint)
  );

  /* INDEXES */
  CREATE INDEX IF NOT EXISTS idx_posts_slug      ON posts(slug);
  CREATE INDEX IF NOT EXISTS idx_posts_category  ON posts(category);
  CREATE INDEX IF NOT EXISTS idx_posts_published ON posts(published, created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_comments_post   ON comments(post_id);
  CREATE INDEX IF NOT EXISTS idx_likes_post      ON likes(post_id);
`);

// ── Seed Demo Data ───────────────────────────────────────────
const seedCount = db.prepare('SELECT COUNT(*) as n FROM posts').get().n;
if (seedCount === 0) {
  const seedPosts = [
    {
      slug: 'plots-near-malshiras-farmhouse-2026-investment-price-guide',
      title: 'Plots near Malshiras Farmhouse: 2026 Investment & Price Guide',
      excerpt: 'Are you dreaming of a weekend getaway or a long-term land investment? Finding the right plots near Malshiras Farmhouse is now easier with our comprehensive 2026 guide.',
      content: `## Why Malshiras is Pune's Next Investment Hotspot

The region around Malshiras has seen remarkable growth in land demand over the past two years. With improved connectivity via the Solapur Highway and proximity to Pandharpur, this area combines spiritual significance with strong investment potential.

## Current Price Bands (2026)

Plots in the Malshiras belt are currently available in the following ranges:

- **Farmhouse plots** (1000–5000 sq. ft.): ₹ 800 – ₹ 1,400 per sq. ft.
- **NA residential plots**: ₹ 1,200 – ₹ 1,800 per sq. ft.
- **Agricultural land** (with conversion potential): ₹ 45 – ₹ 80 lakh per acre

## What Majestic Realties Offers

At Majestic Realties, we have carefully curated farmhouse and residential plot options near Malshiras that offer:

- Collector-approved NA titles
- Clear, encumbrance-free documentation
- Future-ready infrastructure
- Located in Pune's fastest-growing investment corridor

> *"Land is the only investment where supply is permanently fixed. Near Malshiras, demand is just starting to awaken."* — Zaki, Founder, Majestic Realties

## How to Evaluate a Plot

Before investing, verify these critical parameters:

1. **7/12 extract** — confirms current ownership and encumbrances
2. **NA order** — non-agricultural conversion certificate from the Collector
3. **Layout approval** — sanctioned by DTCP or MRDA
4. **Mutation entries** — ensure your name appears in revenue records post-purchase

## Investment Outlook

Analysts predict 18–24% price appreciation in Malshiras over the next 24 months, driven by the Defence Corridor development and rural highway upgrades. Act now before the next price revision.`,
      cover_image: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&q=80',
      category: 'Plot investment',
      tags: JSON.stringify(['Malshiras', 'Farmhouse', 'Investment', 'Pune']),
      author_name: 'Avishkar04',
      author_role: 'NA Plot Specialist',
      author_bio: 'Avishkar has been researching Pune-region land markets since 2019 and specialises in collector-approved NA plots.',
    },
    {
      slug: 'best-plots-near-yavat-residential-2026-investment',
      title: 'Best Plots Near Yavat Residential 2026 Investment',
      excerpt: 'Real estate development around Pune has expanded rapidly in recent years. As property prices in the city continue to rise, buyers are looking at Yavat for affordable residential plots.',
      content: `## Why Yavat Is Attracting Residential Buyers in 2026

Located on the Pune–Solapur National Highway (NH 65), Yavat has emerged as one of the most accessible investment destinations within 40 km of Pune city.

## Key Advantages of Yavat

- **NH 65 Connectivity**: Direct highway access to Hadapsar, Magarpatta, and Pune Airport
- **Industrial Proximity**: Close to Ranjangaon MIDC, generating consistent rental demand
- **Affordable Entry**: Prices still 40–60% lower than comparable Pune suburbs
- **Gated Communities**: Multiple fully-planned township developments now available

## Majestic Realties Projects Near Yavat

### Royal Vista — Premium NA Plots

- Plot sizes from 1100 to 4000 sq. ft.
- Collector-approved NA plots
- Clear titles & future-ready infrastructure
- Located in Pune's fastest-growing investment zone

## 2026 Price Comparison

| Location | Price / sq. ft. | Distance from Pune |
|---|---|---|
| Wagholi | ₹ 4,500+ | 12 km |
| Yavat | ₹ 1,600–2,200 | 38 km |
| Malshiras | ₹ 1,200–1,800 | 85 km |

## Getting Here

Yavat is approximately 35–40 minutes from Hadapsar via NH 65, making it feasible for weekend farmhouse use and practical for those willing to commute.`,
      cover_image: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=900&q=80',
      category: 'Pune Real Estate',
      tags: JSON.stringify(['Yavat', 'Residential', 'NA Plots', 'Investment']),
      author_name: 'Khushi',
      author_role: 'Residential Property Advisor',
      author_bio: 'Khushi covers Pune Metro and peripheral investment zones, helping first-time buyers navigate the NA plot market.',
    },
    {
      slug: 'plots-near-yavat-farmhouse-best-investment-guide-pune-2026',
      title: 'Plots near Yavat Farmhouse: Best Investment Guide Pune 2026',
      excerpt: 'Finding the perfect plots near Yavat Farmhouse has become a top priority for Pune investors seeking value, nature, and long-term appreciation in 2026.',
      content: `## The Rise of Farmhouse Culture Near Yavat

Post-pandemic lifestyle shifts have permanently altered how Pune's urban professionals think about real estate. The farmhouse segment — once a luxury — is now considered a practical, dual-purpose investment.

## What Makes a Good Farmhouse Plot

When evaluating farmhouse plots near Yavat, look for:

1. **Size**: Minimum 2000 sq. ft. for a meaningful farmhouse; 5000 sq. ft. ideal
2. **Access road**: Pucca road minimum 12 feet wide
3. **Water source**: Borewell or canal access nearby
4. **NA status**: Agricultural land without NA order carries conversion risk
5. **Compound wall provision**: Essential for security and boundary definition

## Return on Investment Scenarios

**Conservative scenario** (10% CAGR):
- Buy at ₹ 18 lakh → Worth ₹ 29 lakh in 5 years

**Moderate scenario** (18% CAGR):
- Buy at ₹ 18 lakh → Worth ₹ 41 lakh in 5 years

**Bull scenario** (25% CAGR):
- Buy at ₹ 18 lakh → Worth ₹ 55 lakh in 5 years

> Infrastructure announcements like the Pune Ring Road Phase 2 are the single biggest catalyst for land appreciation in peripheral zones.

## Documentation Checklist

- [ ] 7/12 extract in seller's name
- [ ] 8A (mutation register)
- [ ] NA order from Collector's office
- [ ] Index II (registration document)
- [ ] Encumbrance certificate (EC) — last 30 years
- [ ] Property tax receipts`,
      cover_image: 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=900&q=80',
      category: 'Plot investment',
      tags: JSON.stringify(['Yavat', 'Farmhouse', 'Guide', '2026']),
      author_name: 'Avishkar04',
      author_role: 'NA Plot Specialist',
      author_bio: 'Avishkar has been researching Pune-region land markets since 2019 and specialises in collector-approved NA plots.',
    },
    {
      slug: 'plots-near-mulshi-luxury-is-this-punes-best-investment-2026',
      title: 'Plots Near Mulshi Luxury: Is This Pune\'s Best Investment 2026?',
      excerpt: 'The real estate market surrounding Mulshi has rapidly transformed into one of the most premium addresses in Maharashtra. Direct lake access, panoramic views, and gated communities.',
      content: `## Mulshi: Pune's Most Coveted Address

Nestled in the Sahyadri foothills with direct access to Mulshi Lake, this region has transformed from a weekend retreat into Pune's most aspirational address. Values here have appreciated 35% since 2022.

## What Sets Mulshi Apart

**Natural Advantages:**
- Mulshi Lake shoreline — 48 km of pristine water frontage
- 100+ day average air quality index (vs. Pune city's 65)
- Average maximum temperature 6°C cooler than Pune city

**Infrastructure Upgrades (2024–2026):**
- Kothrud–Mulshi rapid transit corridor (under approval)
- Smart township designation for Pirangut–Mulshi belt
- 24/7 power supply guarantee under Maharashtra Smart Village scheme

## Our Mulshi Luxury Offering

**Plots Near Mulshi — Premium Features:**
- Direct lake access (select plots)
- Panoramic hill views
- Secure gated community
- Bespoke villa design options available
- Contact: +91 86002 77794

## Is It the Right Time to Buy?

Short answer: Yes — but window is closing.

In 2021, similar plots were available at ₹ 2,200/sq. ft. Today they transact at ₹ 4,800–6,500/sq. ft. With the transit corridor announcement expected in Q3 2026, the next price step-up is imminent.

## Legal Note

All Mulshi plots in our portfolio are:
- Maharashtra Industrial Development Corporation (MIDC) free
- No flood-zone designation
- Fully NA-converted with Collectorate order
- RERA registered`,
      cover_image: 'https://images.unsplash.com/photo-1448630360428-65456885c650?w=900&q=80',
      category: 'NA Plots Investment',
      tags: JSON.stringify(['Mulshi', 'Luxury', 'Lake View', 'Premium']),
      author_name: 'Zarina Shaikh',
      author_role: 'Luxury Property Consultant',
      author_bio: 'Zarina specialises in premium and luxury real estate in the Pune-Mulshi-Lavasa belt with over 8 years of experience.',
    },
    {
      slug: 'best-plots-near-yavat-gated-community-2026-investment',
      title: 'Best Plots Near Yavat Gated Community 2026 Investment',
      excerpt: 'Gated community plots near Yavat are now offering Pune investors the ideal balance of security, amenities, and affordability that standalone plots cannot match.',
      content: `## Why Gated Communities Win

The shift from standalone plots to gated community living is the defining trend in Pune's periphery real estate market. Here's why buyers are choosing communities over open land:

- **24/7 Security**: Biometric access, CCTV surveillance, trained security staff
- **Maintained Infrastructure**: Internal roads, drainage, water supply — all maintained by the association
- **Community Amenities**: Clubhouse, swimming pool, children's play area
- **Legal Clarity**: Single layout approval covers all plots — no individual risk
- **Higher Resale Value**: Gated plots command 15–25% premium over open plots

## Yavat Gated Community Options (2026)

### Option 1: Royal Casa by Majestic Realties
- 120 NA plots in a fully planned township
- Plot sizes: 1200 to 3600 sq. ft.
- Price: ₹ 1,850/sq. ft. onwards
- Possession: Ready to register

### Option 2: Budget Tier (Partner Projects)
- 200+ plot township near Yavat bypass
- Starting ₹ 1,200/sq. ft.
- Basic amenities, good for pure investment

## Community Living vs. Open Plot: 10-Year ROI Analysis

| Type | Entry Price | Expected Value (2036) | CAGR |
|---|---|---|---|
| Gated Community Plot | ₹ 20 lakh | ₹ 62 lakh | ~12% |
| Open NA Plot | ₹ 14 lakh | ₹ 38 lakh | ~10.5% |
| City Apartment | ₹ 65 lakh | ₹ 1.1 crore | ~5.4% |

The data strongly favours land in planned communities for wealth creation.`,
      cover_image: 'https://images.unsplash.com/photo-1416331108676-a22ccb276e35?w=900&q=80',
      category: 'Pune Real Estate',
      tags: JSON.stringify(['Gated Community', 'Yavat', 'Township', 'Investment']),
      author_name: 'Khushi',
      author_role: 'Residential Property Advisor',
      author_bio: 'Khushi covers Pune Metro and peripheral investment zones, helping first-time buyers navigate the NA plot market.',
    },
    {
      slug: 'residential-rent-options-rise-expert-realtors-guide',
      title: 'Residential Rent Options Rise: How Expert Realtors Guide Buyers to Perfect Residences & Condominiums',
      excerpt: 'The real estate landscape is evolving at a rapid pace. As residential rent options rise, expert realtors are helping buyers and tenants find ideal residences, apartments, and condominiums.',
      content: `## Why Residential Renting Is Becoming the Smart Choice

Modern renters prefer flexibility over long-term financial commitments. Whether it's an apartment, villa, or condominium, rental demand continues to increase due to:

- Greater affordability
- Mobility and freedom to relocate
- Convenience of ready-to-move options
- Low maintenance responsibilities

Professional realtors ensure seamless property matchmaking, transparent documentation, and hassle-free rental agreements — making renting easier than ever.

## The Rise of Condominiums & Modern Lifestyle Preferences

Condominiums are becoming a preferred residential choice for urban dwellers due to:

- Enhanced security
- Premium lifestyle amenities
- Low-maintenance living
- Prime locations

For corporate professionals and frequent travelers, renting a condominium through trusted realtors is a more practical solution than buying a long-term property.

## Market Outlook: The Next Decade of Residential Renting

Experts predict that the rise in flexible residential options will transform the real estate and rental housing sectors. Realtors will continue guiding tenants toward:

- Luxury condo residences
- Affordable rental apartments
- Compact city studios
- Family-oriented housing communities

## Majestic Realties: Building Trust Through Excellence

At Majestic Realties, we have developed multiple successful projects in Pune, including the iconic Royal Casa — now completely sold out.`,
      cover_image: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=900&q=80',
      category: 'Real Estate Tips',
      tags: JSON.stringify(['Renting', 'Condominiums', 'Market Trends', 'Guide']),
      author_name: 'Avishkar04',
      author_role: 'Market Analyst',
      author_bio: 'Avishkar has been researching Pune-region land markets since 2019 and specialises in collector-approved NA plots.',
    },
    {
      slug: 'real-estate-market-trends-india-opportunities-sellers-owners-investors',
      title: 'Real Estate Market Trends in India – Opportunities for Sellers, Owners & Investors',
      excerpt: 'The Real Estate Market Trends show a dynamic environment with growing demand for land. Pune property market has always been one of India\'s most resilient investment destinations.',
      content: `## India's Real Estate Market in 2026: The Big Picture

India's real estate sector has entered a new growth phase, driven by urbanisation, infrastructure investment, and changing buyer demographics. The total market size is projected to reach USD 1 trillion by 2030.

## Key Trends Reshaping the Market

### 1. Tier-2 City Surge
Cities like Pune, Coimbatore, Nagpur, and Indore are seeing 25–40% higher transaction volumes compared to 2022. Land prices in these markets remain 60–70% below Mumbai and Delhi NCR.

### 2. Industrial Corridor Effect
The Delhi–Mumbai Industrial Corridor (DMIC) and Pune's designation as a Smart City have triggered significant real estate demand in peripheral zones.

### 3. NRI Investment Revival
NRI investments in Indian real estate hit an all-time high in 2025, with Maharashtra receiving the largest share. Weakened rupee makes Indian property exceptionally attractive for dollar-earning diaspora.

## Opportunities by Investor Profile

**First-Time Investors**: NA plots in Pune's periphery (₹ 15–25 lakh range)
**Mid-Ticket Investors**: Gated community plots and commercial land (₹ 40–80 lakh)
**HNI Investors**: Luxury farmhouses, lakefront properties (₹ 1–5 crore)

## What Majestic Realties Recommends

Based on current market data, we recommend:

1. **Lock in land within 40 km of Pune** before the ring road appreciates values
2. **Prioritise NA-converted plots** — they offer 3x liquidity over agricultural land
3. **Choose gated communities** for passive holding — no maintenance headache`,
      cover_image: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=900&q=80',
      category: 'Market Trends',
      tags: JSON.stringify(['Market Trends', 'India', 'Investment', '2026']),
      author_name: 'Zarina Shaikh',
      author_role: 'Market Research Head',
      author_bio: 'Zarina specialises in premium and luxury real estate in the Pune-Mulshi-Lavasa belt with over 8 years of experience.',
    },
    {
      slug: 'why-millennials-prefer-na-plots-over-flats-near-pune',
      title: 'Why Millennials Prefer NA Plots Over Flats Near Pune',
      excerpt: 'The modern generation of homebuyers is currently transforming the real estate sector. While previous buyers favored high-rise apartments, many young professionals now choose NA plots.',
      content: `## The Great Shift: Apartments to Land

A survey of 2,400 Pune homebuyers aged 26–38 conducted in Q4 2025 revealed a striking finding: **63% preferred land investment over apartment purchase** when given an equal budget.

## Why Millennials Are Choosing Land

### Financial Reasoning
- **No EMI trap**: Land purchases are often cash-funded or low-LTV, avoiding 20-year debt
- **Superior ROI**: NA plots near Pune averaged 14.2% CAGR vs 5.8% for apartments (2018–2025)
- **No depreciation**: Unlike structures, land doesn't depreciate

### Lifestyle Reasoning
- **Design freedom**: Build exactly what you want, when you want
- **Farmhouse culture**: Weekend retreat plus appreciating asset
- **Privacy**: No shared walls, lifts, or parking disputes

### Psychological Factors
> "My parents bought a 2BHK in 2005 and it's worth 3x. My friend bought a plot in Talegaon in 2012 for ₹ 8 lakh — it's now worth ₹ 55 lakh. I know which I'm choosing." — Software Engineer, Hinjewadi

## The Anti-Apartment Case (Data-Driven)

| Metric | 2BHK Apartment | NA Plot (40 km) |
|---|---|---|
| Price (2020) | ₹ 60 lakh | ₹ 12 lakh |
| Price (2025) | ₹ 78 lakh | ₹ 28 lakh |
| Return | 30% | 133% |
| Maintenance cost | ₹ 4,000/mo | ₹ 0 |

The numbers speak for themselves.`,
      cover_image: 'https://images.unsplash.com/photo-1553484771-371a605b060b?w=900&q=80',
      category: 'NA Plots Investment',
      tags: JSON.stringify(['Millennials', 'NA Plots', 'Apartments', 'Comparison']),
      author_name: 'Khushi',
      author_role: 'Residential Property Advisor',
      author_bio: 'Khushi covers Pune Metro and peripheral investment zones, helping first-time buyers navigate the NA plot market.',
    },
  ];

  const insertPost = db.prepare(`
    INSERT INTO posts (slug, title, excerpt, content, cover_image, category, tags,
                       author_name, author_role, author_bio)
    VALUES (@slug, @title, @excerpt, @content, @cover_image, @category, @tags,
            @author_name, @author_role, @author_bio)
  `);

  const insertMany = db.transaction((posts) => {
    for (const post of posts) insertPost.run(post);
  });

  insertMany(seedPosts);

  // Seed some comments
  const insertComment = db.prepare(`
    INSERT INTO comments (post_id, name, email, body)
    VALUES (?, ?, ?, ?)
  `);
  insertComment.run(1, 'Ravi Sharma', 'ravi@example.com', 'Very helpful guide! We visited Malshiras last weekend and the plots near the farmhouse zone look very promising.');
  insertComment.run(1, 'Priya Desai', '', 'Can you share the exact distance from Pune station to the Malshiras site? Planning a site visit.');
  insertComment.run(2, 'Amit Kulkarni', 'amit@example.com', 'Is Yavat accessible via PMPML or only private vehicles? Looking to invest but commute is a concern.');

  // Seed some likes
  db.prepare('UPDATE posts SET likes_count = ? WHERE id = ?').run(24, 1);
  db.prepare('UPDATE posts SET likes_count = ? WHERE id = ?').run(17, 2);
  db.prepare('UPDATE posts SET likes_count = ? WHERE id = ?').run(31, 3);
  db.prepare('UPDATE posts SET likes_count = ? WHERE id = ?').run(8, 4);
  db.prepare('UPDATE posts SET views_count = ? WHERE id = ?').run(342, 1);
  db.prepare('UPDATE posts SET views_count = ? WHERE id = ?').run(218, 2);

  console.log('✅ Database seeded with demo data.');
}

// ── Helper: simple fingerprint from IP + UA ──────────────────
function getFingerprint(req) {
  const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress || 'unknown';
  const ua = req.headers['user-agent'] || 'unknown';
  // Simple hash — not cryptographic, just for demo deduplication
  let h = 0;
  for (const c of ip + ua) { h = ((h << 5) - h) + c.charCodeAt(0); h |= 0; }
  return Math.abs(h).toString(36);
}

// ── ROUTES ───────────────────────────────────────────────────

/**
 * GET /api/posts
 * Query params: page, limit, category, search
 */
app.get('/api/posts', (req, res) => {
  const page     = Math.max(1, parseInt(req.query.page)  || 1);
  const limit    = Math.min(20, parseInt(req.query.limit) || 6);
  const offset   = (page - 1) * limit;
  const category = req.query.category || null;
  const search   = req.query.search   || null;

  let where = 'WHERE p.published = 1';
  const params = [];

  if (category && category !== 'All') {
    where += ' AND p.category = ?';
    params.push(category);
  }
  if (search) {
    where += ' AND (p.title LIKE ? OR p.excerpt LIKE ?)';
    params.push(`%${search}%`, `%${search}%`);
  }

  const total = db.prepare(`SELECT COUNT(*) as n FROM posts p ${where}`).get(...params).n;
  const posts = db.prepare(`
    SELECT p.id, p.slug, p.title, p.excerpt, p.cover_image,
           p.category, p.tags, p.author_name, p.author_avatar,
           p.likes_count, p.views_count, p.created_at,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id AND c.approved = 1) AS comments_count
    FROM posts p ${where}
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
  `).all(...params, limit, offset);

  res.json({
    posts,
    pagination: {
      page,
      limit,
      total,
      pages: Math.ceil(total / limit),
    }
  });
});

/**
 * GET /api/posts/latest
 * Returns 5 most recent posts for sidebar
 */
app.get('/api/posts/latest', (req, res) => {
  const posts = db.prepare(`
    SELECT id, slug, title, cover_image, category, created_at, author_name
    FROM posts
    WHERE published = 1
    ORDER BY created_at DESC
    LIMIT 5
  `).all();
  res.json(posts);
});

/**
 * GET /api/categories
 * Returns all categories with post counts
 */
app.get('/api/categories', (req, res) => {
  const cats = db.prepare(`
    SELECT category, COUNT(*) as count
    FROM posts
    WHERE published = 1
    GROUP BY category
    ORDER BY count DESC
  `).all();
  res.json(cats);
});

/**
 * GET /api/posts/:slug
 * Returns full post including rendered HTML content
 */
app.get('/api/posts/:slug', (req, res) => {
  const post = db.prepare(`
    SELECT p.*,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id AND c.approved = 1) AS comments_count
    FROM posts p
    WHERE p.slug = ? AND p.published = 1
  `).get(req.params.slug);

  if (!post) return res.status(404).json({ error: 'Post not found' });

  // Increment view count
  db.prepare('UPDATE posts SET views_count = views_count + 1 WHERE id = ?').run(post.id);
  post.views_count++;

  // Render markdown to HTML
  post.content_html = marked(post.content || '');

  res.json(post);
});

/**
 * GET /api/posts/:id/comments
 */
app.get('/api/posts/:id/comments', (req, res) => {
  const comments = db.prepare(`
    SELECT id, name, body, created_at
    FROM comments
    WHERE post_id = ? AND approved = 1
    ORDER BY created_at ASC
  `).all(req.params.id);
  res.json(comments);
});

/**
 * POST /api/posts/:id/comments
 * Body: { name, email, body }
 */
app.post('/api/posts/:id/comments', (req, res) => {
  const { name, email, body } = req.body;
  const postId = parseInt(req.params.id);

  if (!name || name.trim().length < 2) return res.status(400).json({ error: 'Name required (min 2 chars)' });
  if (!body || body.trim().length < 5) return res.status(400).json({ error: 'Comment body too short' });

  // Check post exists
  const post = db.prepare('SELECT id FROM posts WHERE id = ? AND published = 1').get(postId);
  if (!post) return res.status(404).json({ error: 'Post not found' });

  const result = db.prepare(`
    INSERT INTO comments (post_id, name, email, body)
    VALUES (?, ?, ?, ?)
  `).run(postId, name.trim(), (email || '').trim(), body.trim());

  const comment = db.prepare('SELECT id, name, body, created_at FROM comments WHERE id = ?').get(result.lastInsertRowid);
  res.status(201).json(comment);
});

/**
 * POST /api/posts/:id/like
 * Toggles like based on fingerprint.
 * Returns { liked: bool, likes_count: number }
 */
app.post('/api/posts/:id/like', (req, res) => {
  const postId = parseInt(req.params.id);
  const fp = getFingerprint(req);

  const post = db.prepare('SELECT id, likes_count FROM posts WHERE id = ?').get(postId);
  if (!post) return res.status(404).json({ error: 'Post not found' });

  const existing = db.prepare('SELECT id FROM likes WHERE post_id = ? AND fingerprint = ?').get(postId, fp);

  if (existing) {
    // Unlike
    db.prepare('DELETE FROM likes WHERE post_id = ? AND fingerprint = ?').run(postId, fp);
    db.prepare('UPDATE posts SET likes_count = MAX(0, likes_count - 1) WHERE id = ?').run(postId);
    const updated = db.prepare('SELECT likes_count FROM posts WHERE id = ?').get(postId);
    return res.json({ liked: false, likes_count: updated.likes_count });
  } else {
    // Like
    db.prepare('INSERT INTO likes (post_id, fingerprint) VALUES (?, ?)').run(postId, fp);
    db.prepare('UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?').run(postId);
    const updated = db.prepare('SELECT likes_count FROM posts WHERE id = ?').get(postId);
    return res.json({ liked: true, likes_count: updated.likes_count });
  }
});

/**
 * GET /api/posts/:id/related
 * Returns 4 posts in the same category, excluding current post
 */
app.get('/api/posts/:id/related', (req, res) => {
  const post = db.prepare('SELECT id, category FROM posts WHERE id = ?').get(req.params.id);
  if (!post) return res.status(404).json({ error: 'Post not found' });

  const related = db.prepare(`
    SELECT id, slug, title, cover_image, category, created_at, author_name
    FROM posts
    WHERE category = ? AND id != ? AND published = 1
    ORDER BY created_at DESC
    LIMIT 4
  `).all(post.category, post.id);

  // Fill up to 4 from other categories if needed
  if (related.length < 4) {
    const more = db.prepare(`
      SELECT id, slug, title, cover_image, category, created_at, author_name
      FROM posts
      WHERE id != ? AND id NOT IN (${related.map(() => '?').join(',') || '0'}) AND published = 1
      ORDER BY likes_count DESC
      LIMIT ?
    `).all(post.id, ...related.map(r => r.id), 4 - related.length);
    related.push(...more);
  }

  res.json(related);
});

// ── Start Server ─────────────────────────────────────────────
app.listen(PORT, () => {
  console.log(`🚀 Majestic Blog API running at http://localhost:${PORT}`);
  console.log(`   API:     http://localhost:${PORT}/api/posts`);
  console.log(`   Blog:    http://localhost:${PORT}/index.html`);
});

module.exports = app;