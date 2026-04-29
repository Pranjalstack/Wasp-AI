<div id="cursor-dot"></div>
<canvas id="cursor-trail-canvas"></canvas>

<style>
    /* Base cursor styles */
    * {
        cursor: none !important; /* Hide default cursor everywhere */
    }

    #cursor-dot {
        position: fixed;
        top: 0;
        left: 0;
        width: 8px;
        height: 8px;
        background-color: var(--neon-yellow);
        border-radius: 50%;
        box-shadow: 0 0 10px var(--neon-yellow), 0 0 20px var(--neon-yellow);
        z-index: 10000;
        pointer-events: none;
        transform: translate(-50%, -50%);
    }

    #cursor-trail-canvas {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
    }

    /* Scaling effect on interaction */
    .cursor-hover #cursor-dot {
        transform: translate(-50%, -50%) scale(2);
        background-color: #fff;
        box-shadow: 0 0 15px #fff;
    }
</style>

<script>
    const dot = document.getElementById('cursor-dot');
    const canvas = document.getElementById('cursor-trail-canvas');
    const ctx = canvas.getContext('2d');
    
    let dots = [];
    const maxDots = 25; // Length of the trail

    // Resize canvas
    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    window.addEventListener('mousemove', (e) => {
        const posX = e.clientX;
        const posY = e.clientY;

        // Move the lead dot
        dot.style.left = `${posX}px`;
        dot.style.top = `${posY}px`;

        // Add a new particle to the trail
        dots.push({ x: posX, y: posY, alpha: 1.0 });
    });

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Draw and update trail particles
        for (let i = 0; i < dots.length; i++) {
            const p = dots[i];
            
            // Draw digital trail segments
            ctx.beginPath();
            ctx.arc(p.x, p.y, 2 * p.alpha, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(251, 255, 0, ${p.alpha})`; // Uses your --neon-yellow color
            ctx.fill();

            // Fade out and shrink
            p.alpha -= 0.05;

            if (p.alpha <= 0) {
                dots.splice(i, 1);
                i--;
            }
        }
        requestAnimationFrame(animate);
    }

    animate();

    // Hover detection for interactive elements
    const interactive = document.querySelectorAll('a, button, .file-upload-label, input');
    interactive.forEach(el => {
        el.addEventListener('mouseenter', () => document.body.classList.add('cursor-hover'));
        el.addEventListener('mouseleave', () => document.body.classList.remove('cursor-hover'));
    });
</script>

<footer style="margin-top: 50px; text-align: center; padding: 20px;">
    </footer>