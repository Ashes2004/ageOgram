<?php
$current_route = basename($_SERVER['PHP_SELF']);

// helper function to check active route
function isActive($page) {
    global $current_route;
    return $current_route === $page ? 'bg-[#f0f2f5] rounded-lg' : '';
}
?>

<div class="layout-content-container flex  h-full flex-col  right-0">
  <div class="flex h-full min-h-[700px] flex-col justify-between bg-white p-4">
    <div class="flex flex-col gap-4">
      <div class="flex flex-col gap-2">

        <!-- Home -->
        <div onclick="window.location.href='/AgeOgram/user/dashboard.php'" class="flex items-center gap-3 px-3 py-2 justify-center hover:rounded-lg hover:bg-[#f0f2f5] <?= isActive('dashboard.php') ?>">
          <div class="text-[#111418]">
            <!-- House Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
              <path d="M224,115.55V208a16,16,0,0,1-16,16H168a16,16,0,0,1-16-16V168a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v40a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V115.55a16,16,0,0,1,5.17-11.78l80-75.48a16,16,0,0,1,21.53,0l80,75.48A16,16,0,0,1,224,115.55Z"/>
            </svg>
          </div>
          <p class="text-[#111418] text-sm font-medium">Home</p>
        </div>

        <!-- Profile -->
        <div class="flex items-center gap-3 px-3 py-2 justify-center hover:rounded-lg hover:bg-[#f0f2f5] <?= isActive('profile.php') ?>">
          <div class="text-[#111418]">
            <!-- User Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
              <path d="M230.92,212c-15.23-26.33-38.7-45.21-66.09-54.16a72,72,0,1,0-73.66,0C63.78,166.78,40.31,185.66,25.08,212a8,8,0,1,0,13.85,8c18.84-32.56,52.14-52,89.07-52s70.23,19.44,89.07,52a8,8,0,1,0,13.85-8ZM72,96a56,56,0,1,1,56,56A56.06,56.06,0,0,1,72,96Z"/>
            </svg>
          </div>
          <p class="text-[#111418] text-sm font-medium">Profile</p>
        </div>

        <!-- Explore -->
        <div class="flex items-center gap-3 px-3 py-2 justify-center hover:rounded-lg hover:bg-[#f0f2f5] <?= isActive('explore.php') ?>">
          <div class="text-[#111418]">
            <!-- Compass Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
              <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216ZM172.42,72.84l-64,32a8.05,8.05,0,0,0-3.58,3.58l-32,64A8,8,0,0,0,80,184a8.1,8.1,0,0,0,3.58-.84l64-32a8.05,8.05,0,0,0,3.58-3.58l32-64a8,8,0,0,0-10.74-10.74ZM138,138,97.89,158.11,118,118l40.15-20.07Z"/>
            </svg>
          </div>
          <p class="text-[#111418] text-sm font-medium">Explore</p>
        </div>

        <!-- Age Filter -->
        <div class="flex items-center gap-3 px-3 py-2 justify-center hover:rounded-lg hover:bg-[#f0f2f5] <?= isActive('age-filter.php') ?>">
          <div class="text-[#111418]">
            <!-- Funnel Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
              <path d="M230.6,49.53A15.81,15.81,0,0,0,216,40H40A16,16,0,0,0,28.19,66.76L96,139.17V216a16,16,0,0,0,24.87,13.32l32-21.34A16,16,0,0,0,160,194.66V139.17l67.74-72.32A15.8,15.8,0,0,0,230.6,49.53Z"/>
            </svg>
          </div>
          <p class="text-[#111418] text-sm font-medium">Age Filter</p>
        </div>

        <!-- Add Post -->
        <div onclick="window.location.href='/AgeOgram/post/create.php'" class="flex items-center gap-3 px-3 py-2 justify-center cursor-pointer hover:rounded-lg hover:bg-[#f0f2f5] <?= isActive('create.php') ?>">
          <div class="text-[#111418]">
            <!-- Plus Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <p class="text-[#111418] text-sm font-medium">Add Post</p>
        </div>

        <!-- Logout -->
        <form method="post" class="flex items-center gap-3 px-3 py-2 justify-center hover:rounded-lg hover:bg-[#f0f2f5]">
          <button type="submit" name="logout" class="flex items-center gap-3 w-full justify-center text-left">
            <div class="text-[#111418]">
              <!-- Logout Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
                <path d="M112,216a8,8,0,0,1-8,8H48a16,16,0,0,1-16-16V48A16,16,0,0,1,48,32h56a8,8,0,0,1,0,16H48V208h56A8,8,0,0,1,112,216Zm109.66-93.66-40-40a8,8,0,0,0-11.32,11.32L196.69,120H104a8,8,0,0,0,0,16h92.69l-26.35,26.34a8,8,0,0,0,11.32,11.32l40-40A8,8,0,0,0,221.66,122.34Z"/>
              </svg>
            </div>
            <p class="text-[#111418] text-sm font-medium">Logout</p>
          </button>
        </form>

      </div>
    </div>
  </div>
</div>
