<nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
	id="layout-navbar">
	<div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
		<a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
			<i class="bx bx-menu bx-sm"></i>
		</a>
	</div>

	<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
		<!-- Search -->
		<div class="navbar-nav align-items-center">
			<div class="nav-item d-flex align-items-center text-primary">
				<i class="bx bx-calendar fs-4 lh-0"></i>&nbsp;
				<span class="">{{ date('D') }} {{ date('d M Y') }}</span>
			</div>
		</div>
		<!-- /Search -->

		<ul class="navbar-nav flex-row align-items-center ms-auto">
			<!-- User -->
			<li class="nav-item navbar-dropdown dropdown-user dropdown">
				<a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
					data-bs-toggle="dropdown">
					<div class="avatar avatar-online">
						<img src="{{asset('assets/admin/img/avatars/1.png')}}" alt="admin" class="w-px-40 h-auto rounded-circle" />
					</div>
				</a>
				<ul class="dropdown-menu dropdown-menu-end">
					<li>
						<a class="dropdown-item" href="{{route('admin.profile')}}">
							<div class="d-flex">
								<div class="flex-shrink-0 me-3">
									<div class="avatar avatar-online">
										@if(!empty($user->avatar) && file_exists(public_path('/').$user->avatar))
		                                    <img src="{{asset($user->avatar)}}" alt="User Image" class="w-px-40 h-auto rounded-circle">
		                                @else
		                                    <img src="{{asset('assets/admin/img/avatars/1.png')}}"  alt="User Image" class="w-px-40 h-auto rounded-circle">
		                                @endif
									</div>
								</div>
								<div class="flex-grow-1">
									<span class="fw-medium d-block">{{Auth::user()->full_name}}</span>
									<small class="text-muted">{{ucfirst(Auth::user()->role)}}</small>
								</div>
							</div>
						</a>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.profile')}}">
							<i class="bx bx-user me-2"></i>
							<span class="align-middle">My Profile</span>
						</a>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.change.password')}}">
							<i class="bx bx-key me-2"></i>
							<span class="align-middle">Change Password</span>
						</a>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.logout')}}">
							<i class="bx bx-power-off me-2"></i>
							<span class="align-middle">Log Out</span>
						</a>
					</li>
				</ul>
			</li>
			<!--/ User -->
		</ul>
	</div>
</nav>