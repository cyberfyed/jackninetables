/**
 * Jack Nine Tables - Interactive Table Builder
 */

class PokerTableBuilder {
    constructor() {
        this.svg = document.getElementById('tablePreview');
        this.form = document.getElementById('tableBuilderForm');

        // Default configuration
        this.config = {
            tableStyle: 'racetrack',
            tableSize: '96x48',
            railColor: '#1a1a1a',
            racetrackColor: 'oak',
            surfaceMaterial: 'speedcloth',
            surfaceColor: '#1a472a',
            cupHolders: true,
            cupHolderCount: 10,
            dealerCutout: false
        };

        // Wood color mappings
        this.woodColors = {
            oak: { main: '#9F6427', dark: '#a67c52' },
            walnut: { main: '#5c3d2e', dark: '#3d2314' },
            cherry: { main: '#8b4533', dark: '#6b2c23' }
        };

        this.init();
    }

    init() {
        this.bindEvents();

        // Load saved design if available
        if (typeof loadedDesign !== 'undefined' && loadedDesign) {
            this.loadDesign(loadedDesign);
        }

        // Show/hide racetrack-specific options
        this.toggleRacetrackOptions();

        this.renderTable();
    }

    loadDesign(design) {
        // Update config from loaded design
        this.config = { ...this.config, ...design };

        // Update form controls to match loaded design
        // Table style
        const styleInput = document.querySelector(`input[name="table_style"][value="${design.tableStyle}"]`);
        if (styleInput) styleInput.checked = true;

        // Toggle racetrack color visibility
        this.toggleRacetrackOptions();

        // Racetrack color
        if (design.racetrackColor) {
            const racetrackInput = document.querySelector(`input[name="racetrack_color"][value="${design.racetrackColor}"]`);
            if (racetrackInput) racetrackInput.checked = true;
        }

        // Table size
        document.getElementById('tableSize').value = design.tableSize;

        // Rail color
        const railInput = document.querySelector(`input[name="rail_color"][value="${design.railColor}"]`);
        if (railInput) railInput.checked = true;

        // Surface material
        const materialInput = document.querySelector(`input[name="surface_material"][value="${design.surfaceMaterial}"]`);
        if (materialInput) materialInput.checked = true;

        // Toggle color sets based on material
        this.toggleColorSets();

        // Surface color (try both speedcloth and velveteen)
        const surfaceInput = document.querySelector(`input[name="surface_color"][value="${design.surfaceColor}"]`) ||
                             document.querySelector(`input[name="surface_color_velvet"][value="${design.surfaceColor}"]`);
        if (surfaceInput) surfaceInput.checked = true;

        // Cup holders
        const cupHolderToggle = document.querySelector('input[name="cup_holders"]');
        cupHolderToggle.checked = design.cupHolders;
        document.getElementById('cupHolderCount').style.display = design.cupHolders ? 'block' : 'none';

        // Update cup holder options and set value
        this.updateCupHolderOptions();
        document.querySelector('select[name="cup_holder_count"]').value = design.cupHolderCount;
        this.config.cupHolderCount = design.cupHolderCount;

        // Dealer cutout (if element exists)
        const dealerCutoutInput = document.querySelector('input[name="dealer_cutout"]');
        if (dealerCutoutInput) {
            dealerCutoutInput.checked = design.dealerCutout;
        }

        // Pre-fill design name if editing existing
        if (typeof loadedDesignName !== 'undefined' && loadedDesignName) {
            document.getElementById('designName').value = loadedDesignName;
            // Show saved banner for loaded designs
            this.showSavedBanner(loadedDesignName);
        }
    }

    bindEvents() {
        // Table style
        document.querySelectorAll('input[name="table_style"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.config.tableStyle = e.target.value;
                this.toggleRacetrackOptions();
                this.renderTable();
            });
        });

        // Racetrack color
        document.querySelectorAll('input[name="racetrack_color"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.config.racetrackColor = e.target.value;
                this.renderTable();
            });
        });

        // Table size
        document.getElementById('tableSize').addEventListener('change', (e) => {
            this.config.tableSize = e.target.value;
            this.updateCupHolderOptions();
            this.renderTable();
        });

        // Rail color
        document.querySelectorAll('input[name="rail_color"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.config.railColor = e.target.value;
                this.renderTable();
            });
        });

        // Surface material
        document.querySelectorAll('input[name="surface_material"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.config.surfaceMaterial = e.target.value;
                this.toggleColorSets();
                this.renderTable();
            });
        });

        // Surface colors (speed cloth)
        document.querySelectorAll('input[name="surface_color"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.config.surfaceColor = e.target.value;
                this.renderTable();
            });
        });

        // Surface colors (velveteen)
        document.querySelectorAll('input[name="surface_color_velvet"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.config.surfaceColor = e.target.value;
                this.renderTable();
            });
        });

        // Cup holders toggle
        const cupHolderToggle = document.querySelector('input[name="cup_holders"]');
        cupHolderToggle.addEventListener('change', (e) => {
            this.config.cupHolders = e.target.checked;
            document.getElementById('cupHolderCount').style.display = e.target.checked ? 'block' : 'none';
            this.renderTable();
        });

        // Cup holder count
        document.querySelector('select[name="cup_holder_count"]').addEventListener('change', (e) => {
            this.config.cupHolderCount = parseInt(e.target.value);
            this.renderTable();
        });

        // Dealer cutout (if element exists)
        const dealerCutoutInput = document.querySelector('input[name="dealer_cutout"]');
        if (dealerCutoutInput) {
            dealerCutoutInput.addEventListener('change', (e) => {
                this.config.dealerCutout = e.target.checked;
                this.renderTable();
            });
        }

        // Save design
        const saveBtn = document.getElementById('saveDesign');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.openSaveModal());
        }

        // Request quote
        const quoteBtn = document.getElementById('requestQuote');
        if (quoteBtn) {
            quoteBtn.addEventListener('click', () => this.openQuoteModal());
        }

        // Modal close handlers (only X button and Cancel button, not backdrop)
        document.querySelectorAll('.modal-close, .modal-cancel').forEach(el => {
            el.addEventListener('click', () => this.closeModals());
        });

        // Confirm save
        const confirmSaveBtn = document.getElementById('confirmSave');
        if (confirmSaveBtn) {
            confirmSaveBtn.addEventListener('click', () => this.saveDesign());
        }

        // Submit quote
        const submitQuoteBtn = document.getElementById('submitQuote');
        if (submitQuoteBtn) {
            submitQuoteBtn.addEventListener('click', () => this.submitQuote());
        }

        // Notification OK button
        document.getElementById('notificationOk').addEventListener('click', () => this.closeModals());

        // Quote from saved design button
        const quoteFromSavedBtn = document.getElementById('quoteFromSaved');
        if (quoteFromSavedBtn) {
            quoteFromSavedBtn.addEventListener('click', () => this.openQuoteModal());
        }
    }

    showSavedBanner(designName) {
        const banner = document.getElementById('savedDesignBanner');
        const nameEl = document.getElementById('savedDesignName');
        const actions = document.getElementById('builderActions');

        if (banner && nameEl) {
            nameEl.textContent = `"${designName}"`;
            banner.style.display = 'block';
            if (actions) {
                actions.style.display = 'none';
            }
        }
    }

    showNotification(message, title = 'Notice', type = 'info') {
        const modal = document.getElementById('notificationModal');
        const titleEl = document.getElementById('notificationTitle');
        const messageEl = document.getElementById('notificationMessage');
        const okBtn = document.getElementById('notificationOk');

        titleEl.textContent = title;
        messageEl.textContent = message;

        // Style based on type
        if (type === 'success') {
            titleEl.style.color = 'var(--success)';
            okBtn.className = 'btn btn-secondary';
        } else if (type === 'error') {
            titleEl.style.color = 'var(--error)';
            okBtn.className = 'btn btn-primary';
        } else {
            titleEl.style.color = 'var(--dark)';
            okBtn.className = 'btn btn-primary';
        }

        modal.classList.add('active');
    }

    toggleRacetrackOptions() {
        const racetrackGroup = document.getElementById('racetrackColorGroup');
        const cupHoldersGroup = document.getElementById('cupHoldersGroup');

        if (racetrackGroup) {
            racetrackGroup.style.display = this.config.tableStyle === 'racetrack' ? 'block' : 'none';
        }
        if (cupHoldersGroup) {
            cupHoldersGroup.style.display = this.config.tableStyle === 'racetrack' ? 'block' : 'none';
        }
    }

    toggleColorSets() {
        const speedclothColors = document.querySelector('.speedcloth-colors');
        const velveeteenColors = document.querySelector('.velveteen-colors');

        // Get current selected index before switching
        const currentSet = this.config.surfaceMaterial === 'speedcloth' ? velveeteenColors : speedclothColors;
        const newSet = this.config.surfaceMaterial === 'speedcloth' ? speedclothColors : velveeteenColors;

        const currentInputs = currentSet.querySelectorAll('input');
        const newInputs = newSet.querySelectorAll('input');

        // Find which index was selected in the old set
        let selectedIndex = 0;
        currentInputs.forEach((input, index) => {
            if (input.checked) selectedIndex = index;
        });

        if (this.config.surfaceMaterial === 'speedcloth') {
            speedclothColors.style.display = 'flex';
            velveeteenColors.style.display = 'none';
        } else {
            speedclothColors.style.display = 'none';
            velveeteenColors.style.display = 'flex';
        }

        // Select same index position in new set (or first if out of range)
        const targetIndex = Math.min(selectedIndex, newInputs.length - 1);
        if (newInputs[targetIndex]) {
            newInputs[targetIndex].checked = true;
            this.config.surfaceColor = newInputs[targetIndex].value;
        }
    }

    updateCupHolderOptions() {
        const select = document.querySelector('select[name="cup_holder_count"]');

        // Fixed options: 8 or 10 cup holders for 96x48 table
        select.innerHTML = '<option value="8">8</option><option value="10" selected>10</option>';
        if (this.config.cupHolderCount !== 8 && this.config.cupHolderCount !== 10) {
            this.config.cupHolderCount = 10;
        }
    }

    // Helper function to create stadium/pill shape path
    getStadiumPath(cx, cy, rx, ry) {
        // Stadium shape: semicircle ends with straight sides
        // rx = half width, ry = half height (and radius of end caps)
        const straightLength = rx - ry; // Length of straight section on each side of center

        return `M ${cx - straightLength} ${cy - ry}
                L ${cx + straightLength} ${cy - ry}
                A ${ry} ${ry} 0 0 1 ${cx + straightLength} ${cy + ry}
                L ${cx - straightLength} ${cy + ry}
                A ${ry} ${ry} 0 0 1 ${cx - straightLength} ${cy - ry}
                Z`;
    }

    renderTable() {
        const hasRacetrack = this.config.tableStyle === 'racetrack';

        // Table dimensions in SVG viewBox
        const cx = 400;  // Center X
        const cy = 250;  // Center Y
        const outerRx = 350;  // Outer radius X (horizontal)
        const outerRy = 180;  // Outer radius Y (vertical) - also the end cap radius
        const railWidth = hasRacetrack ? 45 : 35;
        const racetrackOutward = hasRacetrack ? 22 : 0;  // extends into rail area
        const racetrackInward = hasRacetrack ? 33 : 0;   // extends into playing surface (1.5x original)

        let svg = '';

        // Add subtle shadow/depth effect
        svg += `
            <defs>
                <filter id="tableShadow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="5" stdDeviation="8" flood-opacity="0.3"/>
                </filter>
                <filter id="innerShadow">
                    <feOffset dx="0" dy="2"/>
                    <feGaussianBlur stdDeviation="3" result="offset-blur"/>
                    <feComposite operator="out" in="SourceGraphic" in2="offset-blur" result="inverse"/>
                    <feFlood flood-color="black" flood-opacity="0.3" result="color"/>
                    <feComposite operator="in" in="color" in2="inverse" result="shadow"/>
                    <feComposite operator="over" in="shadow" in2="SourceGraphic"/>
                </filter>
                <pattern id="suitPattern" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                    <text x="5" y="22" fill="${this.lightenColor(this.config.surfaceColor, 25)}" font-size="18" opacity="0.5">&#9824;</text>
                    <text x="30" y="22" fill="${this.lightenColor(this.config.surfaceColor, 25)}" font-size="18" opacity="0.5">&#9829;</text>
                    <text x="5" y="45" fill="${this.lightenColor(this.config.surfaceColor, 25)}" font-size="18" opacity="0.5">&#9830;</text>
                    <text x="30" y="45" fill="${this.lightenColor(this.config.surfaceColor, 25)}" font-size="18" opacity="0.5">&#9827;</text>
                </pattern>
            </defs>
        `;

        // Outer rail (main body) - solid color stadium shape
        svg += `
            <path d="${this.getStadiumPath(cx, cy, outerRx, outerRy)}"
                  fill="${this.config.railColor}" filter="url(#tableShadow)"
                  stroke="${this.darkenColor(this.config.railColor, 30)}" stroke-width="2"/>
        `;

        if (hasRacetrack) {
            // Racetrack (wood inlay between rail and felt)
            const racetrackRx = outerRx - railWidth + racetrackOutward;
            const racetrackRy = outerRy - railWidth + racetrackOutward;
            const woodColor = this.woodColors[this.config.racetrackColor] || this.woodColors.oak;

            svg += `
                <path d="${this.getStadiumPath(cx, cy, racetrackRx, racetrackRy)}"
                      fill="${woodColor.main}" stroke="${woodColor.dark}" stroke-width="2"/>
            `;

            // Wood grain texture effect
            svg += `
                <path d="${this.getStadiumPath(cx, cy, racetrackRx, racetrackRy)}"
                      fill="none" stroke="${woodColor.dark}" stroke-width="0.5" stroke-dasharray="4,4" opacity="0.3"/>
            `;
        }

        // Playing surface (inset by racetrack when racetrack style selected)
        const surfaceRx = outerRx - railWidth + racetrackOutward - racetrackInward;
        const surfaceRy = outerRy - railWidth + racetrackOutward - racetrackInward;

        // Base felt color
        svg += `
            <path d="${this.getStadiumPath(cx, cy, surfaceRx, surfaceRy)}"
                  fill="${this.config.surfaceColor}" filter="url(#innerShadow)"/>
        `;

        // Add suit pattern for speed cloth
        if (this.config.surfaceMaterial === 'speedcloth') {
            svg += `
                <path d="${this.getStadiumPath(cx, cy, surfaceRx, surfaceRy)}"
                      fill="url(#suitPattern)" opacity="0.5"/>
            `;
        }

        // Dealer cutout
        if (this.config.dealerCutout) {
            const dealerWidth = 80;
            const dealerDepth = 40;
            svg += `
                <path d="M ${cx - dealerWidth/2} ${cy - surfaceRy}
                         L ${cx - dealerWidth/2} ${cy - surfaceRy - dealerDepth}
                         Q ${cx} ${cy - surfaceRy - dealerDepth - 10} ${cx + dealerWidth/2} ${cy - surfaceRy - dealerDepth}
                         L ${cx + dealerWidth/2} ${cy - surfaceRy}
                         Z"
                      fill="${this.config.railColor}" stroke="${this.darkenColor(this.config.railColor, 20)}" stroke-width="2"/>
            `;
        }

        // Cup holders (only available with racetrack style)
        if (this.config.cupHolders && hasRacetrack) {
            const cupHolderOffset = railWidth - racetrackOutward + racetrackInward/2;  // center of racetrack
            svg += this.renderCupHolders(cx, cy, outerRx, outerRy, cupHolderOffset, true);
        }

        this.svg.innerHTML = svg;
    }

    renderCupHolders(cx, cy, rx, ry, offset, inRacetrack = false) {
        const count = this.config.cupHolderCount;
        let svg = '';
        const holderRadius = inRacetrack ? 12 : 15;  // smaller when in racetrack

        // Cup holders follow the stadium shape, inset by offset
        const holderRy = ry - offset;
        const straightLength = rx - ry;

        // Perimeter sections
        const curveLength = Math.PI * holderRy; // one semicircle
        const straightSideLength = 2 * straightLength; // one straight side
        const totalPerimeter = 2 * curveLength + 2 * straightSideLength;

        const positions = [];

        // Small offset so holders don't land exactly at corners
        const startOffset = totalPerimeter / (count * 2);

        for (let i = 0; i < count; i++) {
            // Evenly distribute around perimeter
            const distance = (startOffset + (i / count) * totalPerimeter) % totalPerimeter;

            let x, y;

            // Section boundaries (going clockwise from top-center)
            const topRightEnd = straightLength;
            const rightCurveEnd = topRightEnd + curveLength;
            const bottomEnd = rightCurveEnd + straightSideLength;
            const leftCurveEnd = bottomEnd + curveLength;

            if (distance <= topRightEnd) {
                // Top straight, right half
                x = cx + distance;
                y = cy - holderRy;
            } else if (distance <= rightCurveEnd) {
                // Right semicircle
                const arcDist = distance - topRightEnd;
                const angle = -Math.PI/2 + (arcDist / curveLength) * Math.PI;
                x = cx + straightLength + holderRy * Math.cos(angle);
                y = cy + holderRy * Math.sin(angle);
            } else if (distance <= bottomEnd) {
                // Bottom straight
                const lineDist = distance - rightCurveEnd;
                x = cx + straightLength - lineDist;
                y = cy + holderRy;
            } else if (distance <= leftCurveEnd) {
                // Left semicircle
                const arcDist = distance - bottomEnd;
                const angle = Math.PI/2 + (arcDist / curveLength) * Math.PI;
                x = cx - straightLength + holderRy * Math.cos(angle);
                y = cy + holderRy * Math.sin(angle);
            } else {
                // Top straight, left half
                const lineDist = distance - leftCurveEnd;
                x = cx - straightLength + lineDist;
                y = cy - holderRy;
            }

            positions.push({ x, y });
        }

        positions.forEach(pos => {
            // Cup holder circle
            svg += `
                <circle cx="${pos.x}" cy="${pos.y}" r="${holderRadius}"
                        fill="#2a2a2a" stroke="#1a1a1a" stroke-width="2"/>
                <circle cx="${pos.x}" cy="${pos.y}" r="${holderRadius - 3}"
                        fill="#1a1a1a"/>
                <circle cx="${pos.x}" cy="${pos.y}" r="${holderRadius - 5}"
                        fill="none" stroke="#3a3a3a" stroke-width="1"/>
            `;
        });

        return svg;
    }

    lightenColor(color, percent) {
        const num = parseInt(color.replace('#', ''), 16);
        const amt = Math.round(2.55 * percent);
        const R = Math.min(255, (num >> 16) + amt);
        const G = Math.min(255, ((num >> 8) & 0x00FF) + amt);
        const B = Math.min(255, (num & 0x0000FF) + amt);
        return '#' + (0x1000000 + R * 0x10000 + G * 0x100 + B).toString(16).slice(1);
    }

    darkenColor(color, percent) {
        const num = parseInt(color.replace('#', ''), 16);
        const amt = Math.round(2.55 * percent);
        const R = Math.max(0, (num >> 16) - amt);
        const G = Math.max(0, ((num >> 8) & 0x00FF) - amt);
        const B = Math.max(0, (num & 0x0000FF) - amt);
        return '#' + (0x1000000 + R * 0x10000 + G * 0x100 + B).toString(16).slice(1);
    }

    getDesignData() {
        return {
            tableStyle: this.config.tableStyle,
            tableSize: this.config.tableSize,
            railColor: this.config.railColor,
            racetrackColor: this.config.racetrackColor,
            surfaceMaterial: this.config.surfaceMaterial,
            surfaceColor: this.config.surfaceColor,
            cupHolders: this.config.cupHolders,
            cupHolderCount: this.config.cupHolderCount,
            dealerCutout: this.config.dealerCutout
        };
    }

    getDesignSummary() {
        const sizeLabels = {
            '84x42': '84" x 42" (8 Players)',
            '96x42': '96" x 42" (10 Players)',
            '96x48': '96" x 48" (8ft x 4ft)',
            '108x48': '108" x 48" (10+ Players)'
        };

        const woodLabels = {
            oak: 'Oak',
            walnut: 'Walnut',
            cherry: 'Cherry'
        };

        const racetrackLine = this.config.tableStyle === 'racetrack'
            ? `<li><span class="label">Racetrack Wood:</span> <span class="value">${woodLabels[this.config.racetrackColor] || 'Oak'}</span></li>`
            : '';

        return `
            <h4>Your Table Configuration:</h4>
            <ul>
                <li><span class="label">Style:</span> <span class="value">${this.config.tableStyle === 'racetrack' ? 'With Racetrack' : 'Standard Rail'}</span></li>
                ${racetrackLine}
                <li><span class="label">Size:</span> <span class="value">${sizeLabels[this.config.tableSize]}</span></li>
                <li><span class="label">Rail Color:</span> <span class="value"><span style="display:inline-block;width:16px;height:16px;background:${this.config.railColor};border-radius:3px;vertical-align:middle;margin-right:5px;border:1px solid #ccc;"></span></span></li>
                <li><span class="label">Surface:</span> <span class="value">${this.config.surfaceMaterial === 'speedcloth' ? 'Suited Speed Cloth' : 'Velveteen'}</span></li>
                <li><span class="label">Surface Color:</span> <span class="value"><span style="display:inline-block;width:16px;height:16px;background:${this.config.surfaceColor};border-radius:3px;vertical-align:middle;margin-right:5px;border:1px solid #ccc;"></span></span></li>
                <li><span class="label">Cup Holders:</span> <span class="value">${this.config.cupHolders ? this.config.cupHolderCount : 'None'}</span></li>
            </ul>
        `;
    }

    openSaveModal() {
        document.getElementById('saveModal').classList.add('active');
        document.getElementById('designName').focus();
    }

    openQuoteModal() {
        document.getElementById('designSummary').innerHTML = this.getDesignSummary();
        document.getElementById('quoteModal').classList.add('active');
    }

    closeModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    }

    async saveDesign() {
        const name = document.getElementById('designName').value.trim();
        if (!name) {
            this.showNotification('Please enter a name for your design.', 'Missing Name', 'error');
            return;
        }

        const data = {
            action: 'save',
            csrf_token: csrfToken,
            name: name,
            design_data: JSON.stringify(this.getDesignData())
        };

        try {
            const response = await fetch(siteUrl + '/api/designs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.closeModals();
                this.showSavedBanner(name);
                this.showNotification('Your design has been saved successfully!', 'Design Saved', 'success');
            } else {
                this.showNotification(result.error || 'Failed to save design.', 'Error', 'error');
            }
        } catch (error) {
            console.error('Save error:', error);
            this.showNotification('An error occurred. Please try again.', 'Error', 'error');
        }
    }

    async submitQuote() {
        const data = {
            action: 'quote',
            csrf_token: csrfToken,
            design_data: JSON.stringify(this.getDesignData()),
            notes: document.getElementById('quoteNotes').value
        };

        try {
            const response = await fetch(siteUrl + '/api/quotes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const responseText = await response.text();
            console.log('Raw response:', responseText);

            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error. Raw response:', responseText);
                this.showNotification('Server error. Check console for details.', 'Error', 'error');
                return;
            }

            if (result.success) {
                this.closeModals();
                this.showNotification('Quote request submitted! We\'ll be in touch soon.', 'Quote Submitted', 'success');
            } else {
                this.showNotification(result.error || 'Failed to submit quote request.', 'Error', 'error');
            }
        } catch (error) {
            console.error('Quote error:', error);
            this.showNotification('An error occurred. Please try again.', 'Error', 'error');
        }
    }
}

// Initialize builder when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.tableBuilder = new PokerTableBuilder();
});
