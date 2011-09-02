using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace PLUSPEOPLE.Pesapi
{
    public class MpesaPayment
    {
        public int id { get; set; }
        public int type { get; set; }
        public int trasfer_direction { get; set; }
        public string reciept { get; set; }
        public DateTime time { get; set; }
        public string phonenumber { get; set; }
        public string name { get; set; }
        public string account { get; set; }
        public int status { get; set; }
        public long amount { get; set; }
        public long post_balance { get; set; }
        public string note { get; set; }


    }
}
