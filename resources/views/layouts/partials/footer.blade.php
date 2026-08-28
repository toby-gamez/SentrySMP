<footer>
    <div class="footer-section">
        <div class="footer-column">
            <h3>Support</h3>
            <ul>
                <li><a href="{{ route('support') }}">Support</a></li>
                <li><a href="https://discord.gg/gXrXMwpuH4" target="_blank">Report Issue</a></li>
                <li><a href="{{ route('vote-for-us') }}">Vote For Us</a></li>
                <li><a href="{{ route('join') }}">How to Join</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3>About Us</h3>
            <ul>
                <li><a href="{{ route('about') }}">About server</a></li>
                <li><a href="{{ route('our-team') }}">Our Team</a></li>
                <li><a href="{{ route('news') }}">News</a></li>
                <li><a href="{{ route('changelog') }}">Changelog</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3>Rules</h3>
            <ul>
                <li><a href="{{ route('discord-rules') }}">Discord Server Rules</a></li>
                <li><a href="{{ route('minecraft-rules') }}">Minecraft Server Rules</a></li>
                <li><a href="{{ route('staff-rules') }}">Staff Rules</a></li>
                <li><a href="{{ route('media-rules') }}">Media Rules</a></li>
                <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}">Terms of Use</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p class="footer-p">© {{ date('Y') }} Sentry SMP. All rights reserved.</p>
        <p class="mojang-notice">We are not affiliated with or endorsed by Mojang, AB.</p>
    </div>
    <p class="web-version">4.0</p>
</footer>
