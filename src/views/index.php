<?php $this->layout('base') ?>
<h2>What is This?</h2>

<p>
This proof-of-concept <a href="https://en.wikipedia.org/wiki/Webring">webring</a> is a way for folks adding <a href="https://indieweb.org/building-blocks">IndieWeb building blocks</a> to their personal websites to find (and be found by) other folks with IndieWeb building blocks on their sites!
</p>

<h2>Sign In</h2>

<p>
Ready to join or already a member? Get started by signing in. The webring works with sites that support <a href="https://indieweb.org/IndieAuth">IndieAuth</a> or <a href="https://indieweb.org/RelMeAuth">RelMeAuth</a>.
</p>

<?php $this->insert('partials/sign-in-form') ?>

<h2>Other Questions?</h2>

<p>
Check our <a href="/terms">Terms of Use and Frequently Asked Questions</a>!
</p>

<div class="h-feed">
  <h2 class="p-name">Recent Changes</h2>
  <ul>
    <li class="h-entry"><strong class="dt-published">2023-05-20</strong> – <em class="p-name">"I'm different!"</em> <span class="e-content">This webring is running on new hardware and software. tl;dr rip emoji, hi php. <a class="u-url" href="https://martymcgui.re/2023/05/20/rebooting--an-indieweb-webring/">[Details and comments]</a></span></li>
    <li class="h-entry"><strong class="dt-published">2021-10-07</strong> – <em class="p-name">Errors checking Let'sEncrypt-protected sites!</em> <span class="e-content"><strong>Update:</strong>Fixed by updating the NodeJS environment. We should be all set! More in this Glitch.com support thread: <a href="https://support.glitch.com/t/curl-cant-connect-to-letsencrypt-sites-anymore/47111/3">Curl can’t connect to letsencrypt sites anymore</a>. I'll update when I know more!</span></li>
    <li class="h-entry"><strong class="dt-published">2019-12-28</strong> – <em class="p-name">You have questions? We have answers!</em> <span class="e-content">We now have a <a href="/terms">Terms of Use and FAQ</a> page! <a class="u-url" href="https://martymcgui.re/2019/12/28/141735/">[Details and comments]</a></span></li>
    <li class="h-entry"><strong class="dt-published">2019-01-19</strong> – <em class="p-name">An IndieWebring Directory!</em> <span class="e-content">Active sites who opt-in can now appear on the <a href="/directory">Directory</a> page! <a class="u-url" href="https://martymcgui.re/2019/01/19/an-indiewebring-directory/">[Details and comments]</a></span></li>    
    <li class="h-entry"><strong class="dt-published">2018-09-29</strong> – <em class="p-name">Webrings without borders!</em> <span class="e-content">New emoji IDs will no longer have country flag emoji. <a class="u-url" href="https://martymcgui.re/2018/09/29/114553/">[Details and comments]</a></span></li>  
  </ul>
</div>

