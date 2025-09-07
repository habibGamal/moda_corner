import { App } from "./index";

declare namespace App.Types {
  /**
   * Promotion data structure returned by the API when applying promotions
   */
  export interface PromotionData {
    discount: number;
    promotion: {
      id: number;
      name: string;
      code: string;
      type: App.Models.Promotion['type'];
    };
  }

  /**
   * API response structure for promotion application
   */
  export interface PromotionApiResponse {
    success: boolean;
    message: string;
    discount: number;
    promotion: {
      id: number;
      name: string;
      code: string;
      type: App.Models.Promotion['type'];
    };
    cartSummary?: App.Models.CartSummary;
  }

  /**
   * API response structure for promotion removal
   */
  export interface PromotionRemovalResponse {
    success: boolean;
    message: string;
    cartSummary?: App.Models.CartSummary;
  }

  /**
   * API response structure for automatic promotions
   */
  export interface AutomaticPromotionResponse {
    success: boolean;
    hasPromotion: boolean;
    discount?: number;
    promotion?: {
      id: number;
      name: string;
      type: App.Models.Promotion['type'];
    };
  }
}
