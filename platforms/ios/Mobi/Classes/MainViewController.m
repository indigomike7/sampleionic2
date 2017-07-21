/*
 Licensed to the Apache Software Foundation (ASF) under one
 or more contributor license agreements.  See the NOTICE file
 distributed with this work for additional information
 regarding copyright ownership.  The ASF licenses this file
 to you under the Apache License, Version 2.0 (the
 "License"); you may not use this file except in compliance
 with the License.  You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing,
 software distributed under the License is distributed on an
 "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 KIND, either express or implied.  See the License for the
 specific language governing permissions and limitations
 under the License.
 */

//
//  MainViewController.h
//  Mobi
//
//  Created by ___FULLUSERNAME___ on ___DATE___.
//  Copyright ___ORGANIZATIONNAME___ ___YEAR___. All rights reserved.
//

#import "MainViewController.h"

@implementation MainViewController

- (id)initWithNibName:(NSString*)nibNameOrNil bundle:(NSBundle*)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Uncomment to override the CDVCommandDelegateImpl used
        // _commandDelegate = [[MainCommandDelegate alloc] initWithViewController:self];
        // Uncomment to override the CDVCommandQueue used
        // _commandQueue = [[MainCommandQueue alloc] initWithViewController:self];
    }
    return self;
}

- (id)init
{
    self = [super init];
    if (self) {
        // Uncomment to override the CDVCommandDelegateImpl used
        // _commandDelegate = [[MainCommandDelegate alloc] initWithViewController:self];
        // Uncomment to override the CDVCommandQueue used
        // _commandQueue = [[MainCommandQueue alloc] initWithViewController:self];
    }
    
    
    self.iAdViewHeight =  0;
    self.webView.scrollView.scrollEnabled = NO;
    
    
    return self;
}

- (void)didReceiveMemoryWarning
{
    // Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];

    // Release any cached data, images, etc that aren't in use.
}

-(void) updateViewBoundSize{
    
    if(!self.webView){
        return;
    }
    
    CGRect bounds = [[UIScreen mainScreen] bounds];
    UIInterfaceOrientation orientation = [[UIApplication sharedApplication] statusBarOrientation];
    NSInteger  width = (int) bounds.size.width;
    NSInteger  height = (int) bounds.size.height;
    
    if (UIDeviceOrientationIsLandscape(orientation)) {
        width = MAX((int) bounds.size.height, (int) bounds.size.width);
        height = MIN((int) bounds.size.height, (int) bounds.size.width);
    }
    
    if(!IsAtLeastiOSVersion(@"7.0")){
        height  = height - 20;
    }
    
    height = height - self.iAdViewHeight;
    
    NSLog(@"update view bound (%d,%d)", width, height);
    
    NSString *JS =  [NSString stringWithFormat:@"viewport = document.querySelector('meta[name=viewport]'); viewport.setAttribute('content', 'user-scalable=no, initial-scale=1.0, maximum-scale=1, minimum-scale=1, width=device-width, height=%d,  target-densitydpi=device-dpi');", height ];
    
    
    [self.webView stringByEvaluatingJavaScriptFromString:JS];
}

- (void)iAdPluginVisible:(NSNotification *)notification
{
    self.iAdViewHeight = 50;
    
    NSLog(@"Receive message iAdPluginVisible");
    [self updateViewBoundSize];    
}

#pragma mark View lifecycle

- (void)viewWillAppear:(BOOL)animated
{
    // View defaults to full size.  If you want to customize the view's size, or its subviews (e.g. webView),
    // you can do so here.

    [super viewWillAppear:animated];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(iAdPluginVisible:) name:@"iAdPluginVisible" object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardWillShow:) name:UIKeyboardWillShowNotification object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardWillHide:) name:UIKeyboardWillHideNotification object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardDidShow:) name:UIKeyboardDidShowNotification object:nil];
    [self updateViewBoundSize];
}

- (void) viewWillDisappear:(BOOL)animated
{
    [super viewWillDisappear:animated];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:UIKeyboardWillShowNotification object:nil];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:UIKeyboardWillHideNotification object:nil];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:UIKeyboardDidShowNotification object:nil];
}

- (void)keyboardDidShow:(NSNotification *)notification
{
    [self updateViewBoundSize];
}

- (void)keyboardWillShow:(NSNotification *)notification
{
    [self updateViewBoundSize];
}

- (void)keyboardWillHide:(NSNotification *)notification
{
    [self updateViewBoundSize];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
}

- (void)viewDidUnload
{
    [super viewDidUnload];
    // Release any retained subviews of the main view.
    // e.g. self.myOutlet = nil;
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    // Return YES for supported orientations
    return [super shouldAutorotateToInterfaceOrientation:interfaceOrientation];
}

/* Comment out the block below to over-ride */

/*
- (UIWebView*) newCordovaViewWithFrame:(CGRect)bounds
{
    return[super newCordovaViewWithFrame:bounds];
}
*/

#pragma mark UIWebDelegate implementation

- (void)didRotateFromInterfaceOrientation:(UIInterfaceOrientation)fromInterfaceOrientation
{
    [super didRotateFromInterfaceOrientation:fromInterfaceOrientation];
    
    [self updateViewBoundSize];
}

- (void)webViewDidFinishLoad:(UIWebView*)theWebView
{
    // Black base color for background matches the native apps
    theWebView.backgroundColor = [UIColor blackColor];
    
    theWebView.scrollView.showsVerticalScrollIndicator = NO;
    theWebView.scrollView.showsHorizontalScrollIndicator = NO;
    
    
    // =============== admob ==========================
    
    CGRect viewBounds = CGRectMake([[UIScreen mainScreen] applicationFrame].origin.x, [[UIScreen mainScreen] applicationFrame].origin.y - 20, [[UIScreen mainScreen] applicationFrame].size.width, [[UIScreen mainScreen] applicationFrame].size.height-CGSizeFromGADAdSize(kGADAdSizeBanner).height + 20);
    
    theWebView.frame = viewBounds;
    
    CGPoint origin = CGPointMake(0.0,
                                 
                                 self.view.frame.size.height -
                                 
                                 CGSizeFromGADAdSize(kGADAdSizeBanner).height);
    
    bannerView_ = [[GADBannerView alloc] initWithAdSize:kGADAdSizeBanner origin:origin];
    
    bannerView_.adUnitID = @"ca-app-pub-6253688624927798/6719261105";
    
    bannerView_.rootViewController = self;
    
    self.iAdViewHeight = 50;
    
    [self.view addSubview:bannerView_];
    GADRequest *request = [GADRequest request];
    [bannerView_ setDelegate:self];
    
    [bannerView_ loadRequest:request];
    
    
    // =============== admob ==========================
    
    [self updateViewBoundSize];
    
    return [super webViewDidFinishLoad:theWebView];
}

/* Comment out the block below to over-ride */

/*

- (void) webViewDidStartLoad:(UIWebView*)theWebView
{
    return [super webViewDidStartLoad:theWebView];
}

- (void) webView:(UIWebView*)theWebView didFailLoadWithError:(NSError*)error
{
    return [super webView:theWebView didFailLoadWithError:error];
}
 */
- (BOOL) webView:(UIWebView*)theWebView shouldStartLoadWithRequest:(NSURLRequest*)request navigationType:(UIWebViewNavigationType)navigationType
{
    [self updateViewBoundSize];
    
    return [super webView:theWebView shouldStartLoadWithRequest:request navigationType:navigationType];
}


@end

@implementation MainCommandDelegate

/* To override the methods, uncomment the line in the init function(s)
   in MainViewController.m
 */

#pragma mark CDVCommandDelegate implementation

- (id)getCommandInstance:(NSString*)className
{
    return [super getCommandInstance:className];
}

- (NSString*)pathForResource:(NSString*)resourcepath
{
    return [super pathForResource:resourcepath];
}

@end

@implementation MainCommandQueue

/* To override, uncomment the line in the init function(s)
   in MainViewController.m
 */
- (BOOL)execute:(CDVInvokedUrlCommand*)command
{
    return [super execute:command];
}

@end
