
    </main>

    <style>
        /* ==================================
           MOZA GROUP - DARK FOOTER
        ================================== */

        .site-footer {
            background: #080808;
            color: #a1a1aa;
            padding-top: 75px;
            margin-top: 80px;
            border-top: 1px solid #222;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 50px;
            padding-bottom: 65px;
        }

        .footer-brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            color: #fff;
            font-size: 25px;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: -0.8px;
        }

        .footer-brand-dot {
            width: 12px;
            height: 12px;
            background: #e11d48;
            border-radius: 3px;
            display: inline-block;
            box-shadow: 0 0 15px rgba(225, 29, 72, .5);
            flex-shrink: 0;
        }

        .footer-about p {
            color: #888;
            font-size: 14px;
            line-height: 1.9;
            margin-top: 22px;
            max-width: 340px;
        }

        .footer-line {
            width: 45px;
            height: 3px;
            background: #e11d48;
            margin-top: 25px;
            border-radius: 10px;
        }

        .footer-tagline {
            display: block;
            margin-top: 15px;
            font-size: 11px;
            color: #666;
            letter-spacing: 1px;
        }

        .footer-column h2 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .footer-column h2::after {
            content: "";
            display: block;
            width: 25px;
            height: 2px;
            background: #e11d48;
            margin-top: 12px;
        }

        .footer-column p {
            font-size: 13px;
            line-height: 1.8;
            color: #888;
            margin-bottom: 15px;
            overflow-wrap: anywhere;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .footer-links a {
            color: #999;
            text-decoration: none;
            font-size: 13px;
            transition: color .25s ease, padding .25s ease;
            overflow-wrap: anywhere;
        }

        .footer-links a:hover {
            color: #e11d48;
            padding-left: 5px;
        }

        .footer-bottom {
            border-top: 1px solid #222;
            background: #050505;
        }

        .footer-bottom-inner {
            min-height: 75px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .footer-bottom span {
            font-size: 12px;
            color: #666;
        }

        .footer-bottom a {
            color: #999;
            font-size: 12px;
            text-decoration: none;
            transition: color .2s;
        }

        .footer-bottom a:hover {
            color: #e11d48;
        }

        /* WhatsApp */
        .floating-whatsapp {
            background: #e11d48;
            color: #fff;
            border-radius: 50px;
            padding: 13px 22px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 5px 25px rgba(225, 29, 72, .3);
            transition: .25s ease;
        }

        .floating-whatsapp:hover {
            background: #be123c;
            transform: translateY(-3px);
        }

        /* Tablet */
        @media (max-width: 900px) {
            .footer-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 40px 30px;
            }
        }

        /* Mobile */
        @media (max-width: 600px) {
            .site-footer {
                padding-top: 45px;
                margin-top: 50px;
            }

            .footer-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 35px 20px;
                padding-bottom: 40px;
            }

            .footer-about {
                grid-column: 1 / -1;
            }

            .footer-brand {
                font-size: 22px;
            }

            .footer-about p {
                font-size: 13px;
                margin-top: 15px;
            }

            .footer-column h2 {
                font-size: 14px;
                margin-bottom: 18px;
            }

            .footer-links {
                gap: 13px;
            }

            .footer-links a {
                font-size: 12px;
            }

            .footer-column p {
                font-size: 12px;
            }

            .footer-bottom-inner {
                min-height: auto;
                padding-top: 20px;
                padding-bottom: 20px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .footer-bottom span {
                font-size: 11px;
                line-height: 1.7;
            }
        }

        /* Small Mobile */
        @media (max-width: 360px) {
            .footer-grid {
                column-gap: 12px;
            }

            .footer-brand {
                font-size: 19px;
            }
        }
    </style>

    <footer class="site-footer">
        <div class="container footer-grid">

            <!-- Brand -->
            <div class="footer-about">
                <a class="footer-brand" href="<?= e(url()) ?>">
                    <span class="footer-brand-dot"></span>
                    <?= e($companyName) ?>
                </a>

                <p>
                    <?= e(
                        $company['ringkasan']
                        ?: 'Informasi perusahaan dan unit usaha Moza Group.'
                    ) ?>
                </p>

                <div class="footer-line"></div>

                <span class="footer-tagline">
                    Building Growth, Creating Opportunities.
                </span>
            </div>

            <!-- Navigasi -->
            <div class="footer-column">
                <h2>Jelajahi</h2>

                <ul class="footer-links">
                    <li>
                        <a href="<?= e(url('tentang.php')) ?>">Tentang Kami</a>
                    </li>
                    <li>
                        <a href="<?= e(url('unit-usaha.php')) ?>">Unit Usaha</a>
                    </li>
                    <li>
                        <a href="<?= e(url('produk.php')) ?>">Produk</a>
                    </li>
                    <li>
                        <a href="<?= e(url('kemitraan.php')) ?>">Kemitraan</a>
                    </li>
                </ul>
            </div>

            <!-- Kontak -->
            <div class="footer-column">
                <h2>Hubungi Kami</h2>

                <?php if (!empty($settings['alamat'])): ?>
                    <p><?= text_to_html($settings['alamat']) ?></p>
                <?php endif; ?>

                <ul class="footer-links">
                    <?php
                    $email = (string) ($settings['email'] ?? '');
                    ?>

                    <?php if ($email !== '' && validate_email($email) === null): ?>
                        <li>
                            <a href="<?= e('mailto:' . $email) ?>">
                                <?= e($email) ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($contactWhatsapp !== null): ?>
                        <li>
                            <a
                                href="<?= e($contactWhatsapp) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                WhatsApp
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Media Sosial -->
            <div class="footer-column">
                <h2>Ikuti Kami</h2>

                <ul class="footer-links">
                    <?php
                    $socials = [
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'tiktok' => 'TikTok',
                        'youtube' => 'YouTube',
                    ];
                    ?>

                    <?php foreach ($socials as $field => $label): ?>
                        <?php
                        $socialUrl = (string) ($settings[$field] ?? '');
                        ?>

                        <?php if ($socialUrl !== '' && validate_url($socialUrl) === null): ?>
                            <li>
                                <a
                                    href="<?= e($socialUrl) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?= e($label) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <div class="container footer-bottom-inner">

                <span>
                    &copy; <?= date('Y') ?> <?= e($companyName) ?>.
                    Seluruh hak cipta dilindungi.
                </span>

                <a href="<?= e(url('admin/login.php')) ?>">
                    Login Admin <span>↗</span>
                </a>

            </div>
        </div>
    </footer>

    <?php if ($contactWhatsapp !== null): ?>
        <a
            class="floating-whatsapp"
            href="<?= e($contactWhatsapp) ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Hubungi Moza Group melalui WhatsApp"
        >
            WhatsApp
        </a>
    <?php endif; ?>

    <script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
