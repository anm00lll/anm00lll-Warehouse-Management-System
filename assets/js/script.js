 /* ============================================================================ */
/* == Main JavaScript for the Warehouse Management System                    == */
/* ============================================================================ */

document.addEventListener('DOMContentLoaded', function () {

    /**
     * Sidebar Active Link Handler
     * ---------------------------
     * This function gets the current page URL and adds the 'active' class
     * to the corresponding link in the sidebar navigation to highlight it.
     */
    const setActiveSidebarLink = () => {
        const currentPage = window.location.pathname.split('/').pop();
        // If on the root, default to home.php
        const activePage = currentPage === '' ? 'home.php' : currentPage;
        
        const navLinks = document.querySelectorAll('.sidebar-nav a');
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href').split('/').pop();
            if (linkPage === activePage) {
                link.classList.add('active');
            }
        });
    };

    /**
     * Dashboard Sales Chart
     * ---------------------
     * This function checks if the sales chart canvas element exists on the page
     * (it will only be on home.php) and, if so, renders the bar chart using Chart.js.
     */
    const initializeSalesChart = () => {
        const ctx = document.getElementById('salesChart');
        if (!ctx) {
            // If the canvas element is not found, do nothing.
            return;
        }

        // In a real application, this data would be fetched from the server via PHP/AJAX.
        // For this example, we generate random data for demonstration.
        const labels = Array.from({ length: 30 }, (_, i) => i + 1); // Labels for 30 days
        const dataPoints = Array.from({ length: 30 }, () => Math.floor(Math.random() * 500) + 50);

        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales ($)',
                    data: dataPoints,
                    backgroundColor: 'rgba(74, 144, 226, 0.7)',
                    borderColor: 'rgba(74, 144, 226, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    hoverBackgroundColor: 'rgba(74, 144, 226, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#eef2f7' // Using a color from our CSS variables
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#34495e', // Dark grey from CSS variables
                        titleFont: { size: 14, family: 'Poppins' },
                        bodyFont: { size: 12, family: 'Poppins' },
                        padding: 10,
                        cornerRadius: 5
                    }
                }
            }
        });
    };

    // --- Initialize all functions ---
    setActiveSidebarLink();
    initializeSalesChart();

});

