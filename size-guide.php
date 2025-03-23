<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Size Guide - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --accent-color: #f39c12;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-text: #666;
            --lighter-text: #999;
            --border-color: #e1e1e1;
            --light-bg: #f9f9f9;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --input-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .size-guide-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .size-guide-header {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .size-guide-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .size-guide-header p {
            color: var(--light-text);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .size-categories {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.2s forwards;
        }

        .category-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            background: var(--white);
            color: var(--text-color);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .size-guide-content {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.3s forwards;
        }

        .measurement-tips {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
        }

        .tips-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tips-list li {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
            color: var(--light-text);
            line-height: 1.6;
        }

        .tips-list li i {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-top: 3px;
        }

        .size-chart {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        .chart-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .size-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .size-table th,
        .size-table td {
            padding: 15px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .size-table th {
            background: var(--light-bg);
            color: var(--text-color);
            font-weight: 600;
        }

        .size-table td {
            color: var(--light-text);
        }

        .size-table tr:nth-child(even) {
            background: var(--light-bg);
        }

        .measurement-diagram {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .diagram-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .diagram-image {
            max-width: 100%;
            height: auto;
            margin: 20px 0;
            border-radius: 10px;
        }

        .size-note {
            background: rgba(243, 156, 18, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .size-note i {
            color: var(--accent-color);
            font-size: 1.2rem;
            margin-top: 3px;
        }

        .size-note p {
            color: var(--accent-color);
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .category-content {
            display: none;
        }

        .category-content.active {
            display: block;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .size-guide-header h1 {
                font-size: 2rem;
            }

            .category-btn {
                font-size: 0.9rem;
                padding: 10px 20px;
            }

            .size-table {
                font-size: 0.9rem;
            }
        }

        /* Page Loader Styles */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .page-loader.hide {
            opacity: 0;
            visibility: hidden;
        }

        .loader {
            width: 70px;
            height: 70px;
            position: relative;
        }

        .loader-circle {
            width: 100%;
            height: 100%;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s infinite linear;
        }

        .loader-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 30px;
            animation: pulse 1.5s infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(0.8); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
            100% { transform: translate(-50%, -50%) scale(0.8); }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-icon">📏</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="size-guide-container">
        <div class="size-guide-header">
            <h1>
                <span>Size Guide</span>
                📏
            </h1>
            <p>Find your perfect fit with our comprehensive size guide and measurement instructions</p>
        </div>

        <div class="size-categories">
            <button class="category-btn active" data-category="clothing">
                👕 Clothing
            </button>
            <button class="category-btn" data-category="shoes">
                👟 Shoes
            </button>
            <button class="category-btn" data-category="accessories">
                👜 Accessories
            </button>
        </div>

        <div class="size-guide-content">
            <!-- Clothing Sizes -->
            <div class="category-content active" id="clothing">
                <div class="measurement-tips">
                    <h2 class="tips-title">
                        <span>How to Measure</span>
                        📌
                    </h2>
                    <ul class="tips-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Use a flexible tape measure and measure your body directly, not over clothes</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Stand straight and relaxed while taking measurements</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>If measuring yourself, do so in front of a mirror to ensure the tape measure is level</span>
                        </li>
                    </ul>
                </div>

                <div class="size-chart">
                    <h2 class="chart-title">
                        <span>Clothing Size Chart</span>
                        👚
                    </h2>
                    <table class="size-table">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Chest (cm)</th>
                                <th>Waist (cm)</th>
                                <th>Hip (cm)</th>
                                <th>US Size</th>
                                <th>UK Size</th>
                                <th>EU Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>XS</td>
                                <td>82-87</td>
                                <td>64-69</td>
                                <td>88-93</td>
                                <td>2-4</td>
                                <td>6-8</td>
                                <td>34-36</td>
                            </tr>
                            <tr>
                                <td>S</td>
                                <td>87-92</td>
                                <td>69-74</td>
                                <td>93-98</td>
                                <td>6-8</td>
                                <td>10-12</td>
                                <td>38-40</td>
                            </tr>
                            <tr>
                                <td>M</td>
                                <td>92-97</td>
                                <td>74-79</td>
                                <td>98-103</td>
                                <td>10-12</td>
                                <td>14-16</td>
                                <td>42-44</td>
                            </tr>
                            <tr>
                                <td>L</td>
                                <td>97-102</td>
                                <td>79-84</td>
                                <td>103-108</td>
                                <td>14-16</td>
                                <td>18-20</td>
                                <td>46-48</td>
                            </tr>
                            <tr>
                                <td>XL</td>
                                <td>102-107</td>
                                <td>84-89</td>
                                <td>108-113</td>
                                <td>18-20</td>
                                <td>22-24</td>
                                <td>50-52</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Shoe Sizes -->
            <div class="category-content" id="shoes">
                <div class="measurement-tips">
                    <h2 class="tips-title">
                        <span>How to Measure Your Foot</span>
                        📏
                    </h2>
                    <ul class="tips-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Measure your feet in the afternoon (feet naturally expand during the day)</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Stand while measuring and wear socks similar to what you'll wear with the shoes</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Measure both feet and use the larger measurement</span>
                        </li>
                    </ul>
                </div>

                <div class="size-chart">
                    <h2 class="chart-title">
                        <span>Shoe Size Chart</span>
                        👞
                    </h2>
                    <table class="size-table">
                        <thead>
                            <tr>
                                <th>Foot Length (cm)</th>
                                <th>EU Size</th>
                                <th>UK Size</th>
                                <th>US Size (Men)</th>
                                <th>US Size (Women)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>23.5</td>
                                <td>37</td>
                                <td>4</td>
                                <td>5</td>
                                <td>6.5</td>
                            </tr>
                            <tr>
                                <td>24.1</td>
                                <td>38</td>
                                <td>5</td>
                                <td>6</td>
                                <td>7.5</td>
                            </tr>
                            <tr>
                                <td>24.8</td>
                                <td>39</td>
                                <td>6</td>
                                <td>7</td>
                                <td>8.5</td>
                            </tr>
                            <tr>
                                <td>25.4</td>
                                <td>40</td>
                                <td>7</td>
                                <td>8</td>
                                <td>9.5</td>
                            </tr>
                            <tr>
                                <td>26</td>
                                <td>41</td>
                                <td>8</td>
                                <td>9</td>
                                <td>10.5</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Accessories -->
            <div class="category-content" id="accessories">
                <div class="measurement-tips">
                    <h2 class="tips-title">
                        <span>Accessories Guide</span>
                        👜
                    </h2>
                    <ul class="tips-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>For belts, measure your natural waistline where you typically wear your belt</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>For hats, measure the circumference of your head about 1cm above your ears</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>For gloves, measure around your palm at the widest point (excluding thumb)</span>
                        </li>
                    </ul>
                </div>

                <div class="size-chart">
                    <h2 class="chart-title">
                        <span>Accessories Size Chart</span>
                        🧤
                    </h2>
                    <table class="size-table">
                        <thead>
                            <tr>
                                <th>Item Type</th>
                                <th>Measurement Point</th>
                                <th>S</th>
                                <th>M</th>
                                <th>L</th>
                                <th>XL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Belts</td>
                                <td>Waist (cm)</td>
                                <td>70-80</td>
                                <td>80-90</td>
                                <td>90-100</td>
                                <td>100-110</td>
                            </tr>
                            <tr>
                                <td>Hats</td>
                                <td>Head Circumference (cm)</td>
                                <td>55-56</td>
                                <td>57-58</td>
                                <td>59-60</td>
                                <td>61-62</td>
                            </tr>
                            <tr>
                                <td>Gloves</td>
                                <td>Palm Width (cm)</td>
                                <td>17-18</td>
                                <td>19-20</td>
                                <td>21-22</td>
                                <td>23-24</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="size-note">
                <i class="fas fa-info-circle"></i>
                <p>Please note that these measurements are general guidelines. For the best fit, we recommend checking the specific size chart provided with each product, as sizes may vary between brands and styles.</p>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page Loader
            const pageLoader = document.querySelector('.page-loader');
            window.addEventListener('load', function() {
                setTimeout(function() {
                    pageLoader.classList.add('hide');
                }, 800);
            });

            // Category Toggle
            const categoryBtns = document.querySelectorAll('.category-btn');
            const categoryContents = document.querySelectorAll('.category-content');
            
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const category = btn.getAttribute('data-category');
                    
                    // Update active button
                    categoryBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    // Show/hide content
                    categoryContents.forEach(content => {
                        if (content.id === category) {
                            content.classList.add('active');
                        } else {
                            content.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html> 